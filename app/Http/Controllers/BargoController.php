<?php

namespace App\Http\Controllers;

use App\Models\EventApprovalFlow;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;

class BargoController extends Controller
{
    public function dashboard()
    {
        $pendingReviews = EventApprovalFlow::where('approver_role', 'BARGO')
            ->where('status', 'pending')
            ->count();

        $approved = EventApprovalFlow::where('approver_role', 'BARGO')
            ->where('status', 'approved')
            ->count();

        $rejected = EventApprovalFlow::where('approver_role', 'BARGO')
            ->where('status', 'rejected')
            ->count();

        return view('bargo.dashboard', compact('pendingReviews', 'approved', 'rejected'));
    }

    public function pending()
    {
        $pendingReviews = EventApprovalFlow::with(['permit.organization'])
            ->where('approver_role', 'BARGO')
            ->where('status', 'pending')
            ->latest('created_at')
            ->get();

        return view('bargo.events.pending', compact('pendingReviews'));
    }

    public function approved()
    {
        $approvedReviews = EventApprovalFlow::with(['permit.organization'])
            ->where('approver_role', 'BARGO')
            ->where('status', 'approved')
            ->latest('approved_at')
            ->get();

        return view('bargo.events.approved', compact('approvedReviews'));
    }

    public function rejected()
    {
        $rejectedReviews = EventApprovalFlow::with(['permit.organization'])
            ->where('approver_role', 'BARGO')
            ->where('status', 'rejected')
            ->latest('updated_at')
            ->get();

        return view('bargo.events.rejected', compact('rejectedReviews'));
    }

    public function history()
    {
        $historyReviews = EventApprovalFlow::with(['permit.organization'])
            ->where('approver_role', 'BARGO')
            ->whereIn('status', ['approved', 'rejected'])
            ->latest('updated_at')
            ->get();

        return view('bargo.events.history', compact('historyReviews'));
    }

    public function viewPermitPdf($hashed_id)
    {
        $permit = Permit::where('hashed_id', $hashed_id)->firstOrFail();

        if (!$permit->pdf_data) {
            abort(404, 'PDF not generated yet.');
        }

        return response($permit->pdf_data, 200)
            ->header('Content-Type', 'application/pdf')
            ->header('Content-Disposition', 'inline; filename="Permit_' . $hashed_id . '.pdf"');
    }

    // BARGO APPROVE – with digital signature (upload or draw)
    public function approve(Request $request, $approval_id)
    {
        $flow = EventApprovalFlow::findOrFail($approval_id);

        // Security: Only BARGO role can approve BARGO step
        if ($flow->approver_role !== 'BARGO' || $flow->status !== 'pending') {
            abort(403);
        }

        $permit = $flow->permit;
        if (!$permit || !$permit->pdf_data) {
            return back()->with('error', 'PDF not found.');
        }

        // Get BARGO full name
        $bargoName = strtoupper(trim(Auth::user()->name ?? 'BARGO OFFICER'));

        // Prepare temp directory
        $tempDir = storage_path('app/temp');
        if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

        $tempPdfPath = $tempDir . "/permit_{$permit->hashed_id}.pdf";
        file_put_contents($tempPdfPath, $permit->pdf_data);

        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($tempPdfPath);

        $signaturePath = null;

        // Option 1: Uploaded signature
        if ($request->hasFile('signature_upload') && $request->file('signature_upload')->isValid()) {
            $signaturePath = $request->file('signature_upload')->getRealPath();
        }
        // Option 2: Drawn signature (canvas)
        elseif ($request->filled('signature_data')) {
            $imgData = preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data);
            $imgData = str_replace(' ', '+', $imgData);
            $data = base64_decode($imgData);
            $signaturePath = $tempDir . "/bargo_sig_{$approval_id}.png";
            file_put_contents($signaturePath, $data);
        }
        // Option 3: Use saved signature from profile (optional)
        elseif (Auth::user()->signature && Storage::disk('public')->exists(Auth::user()->signature)) {
            $signaturePath = storage_path('app/public/' . Auth::user()->signature);
        }

        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tplIdx = $pdf->importPage($pageNo);
            $size = $pdf->getTemplateSize($tplIdx);
            $pdf->AddPage($size['orientation'], $size['width'], $size['height']);
            $pdf->useTemplate($tplIdx);

            // Only sign on first page (adjust Y position as needed)
            if ($pageNo === 1 && $signaturePath && file_exists($signaturePath)) {
                $sigX = 80;      // Adjust X position
                $sigY = 150;      // Adjust Y position for BARGO signature
                $sigWidth = 50;
                list($origW, $origH) = getimagesize($signaturePath);
                $sigHeight = ($sigWidth / $origW) * $origH;

                $pdf->Image($signaturePath, $sigX, $sigY, $sigWidth, $sigHeight);

                // Print name below signature
                $pdf->SetFont('Helvetica', 'B', 10);
                $pdf->SetXY($sigX - 5, $sigY + $sigHeight + 3);
                $pdf->Cell($sigWidth + 10, 8, $bargoName, 0, 1, 'C');
            }
        }

        // Save signed PDF
        $outputPath = $tempDir . "/bargo_signed_{$permit->hashed_id}.pdf";
        $pdf->Output($outputPath, 'F');

        // Update permit with new signed PDF
        $permit->pdf_data = file_get_contents($outputPath);
        $permit->save();

        // Update approval flow
        $flow->update([
            'status' => 'approved',
            'approver_id' => Auth::id(),
            'approver_name' => $bargoName,
            'approved_at' => now(),
        ]);

        // Cleanup
        @unlink($tempPdfPath);
        @unlink($outputPath);
        if ($signaturePath && str_contains($signaturePath, 'bargo_sig_')) {
            @unlink($signaturePath);
        }

        return back()->with('success', 'Permit successfully approved and signed by BARGO.');
    }

    // BARGO REJECT
    public function reject(Request $request, $approval_id)
    {
        $request->validate([
            'comments' => 'required|string|max:1000'
        ]);

        $flow = EventApprovalFlow::findOrFail($approval_id);

        if ($flow->approver_role !== 'BARGO' || $flow->status !== 'pending') {
            abort(403);
        }

        $flow->update([
            'status' => 'rejected',
            'comments' => $request->comments,
            'approver_id' => Auth::id(),
            'approver_name' => strtoupper(Auth::user()->name ?? 'BARGO'),
            'updated_at' => now(),
        ]);

        return back()->with('error', 'Permit has been rejected.');
    }
}
