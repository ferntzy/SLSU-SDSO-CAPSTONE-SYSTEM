<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use App\Models\Organization;

class BargoController extends Controller
{
  public function dashboard()
  {
    $bargoId = Auth::user()->user_id;

    // Get all permits where BARGO is the next approver
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
    $pendingReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'BARGO')
      ->where('status', 'pending')
      ->orderBy('created_at', 'asc')
      ->get();

    return view('bargo.events.pending', compact('pendingReviews'));
  }
  public function approvals()
  {
    $pendingPermits = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'BARGO')
      ->where('status', 'pending')
      ->orderBy('created_at', 'desc')
      ->get();

    return view('bargo.approvals', compact('pendingPermits'));
  }

  public function viewPermitPdf($hashed_id)
  {
    $permit = Permit::where('hashed_id', $hashed_id)->first();

    if (!$permit || !$permit->pdf_data) {
      abort(404, 'PDF not available.');
    }

    return response($permit->pdf_data)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="permit.pdf"');
  }

  public function approve(Request $request, $approval_id)
  {
    $flow = EventApprovalFlow::findOrFail($approval_id);
    $permit = $flow->permit;

    if (!$permit->pdf_data) {
      return back()->with('error', 'No PDF found.');
    }

    $bargoName = strtoupper(Auth::user()->name ?? 'UNKNOWN');

    $tempDir = storage_path('app/temp');
    if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

    $tempPdfPath = $tempDir . "/temp_permit_{$permit->hashed_id}.pdf";
    file_put_contents($tempPdfPath, $permit->pdf_data);

    $pdf = new FPDI();
    $pdf->AddPage();
    $pdf->setSourceFile($tempPdfPath);
    $templateId = $pdf->importPage(1);
    $pdf->useTemplate($templateId, 0, 0, 210);

    // Signature Logic
    $signaturePath = null;
    if ($request->hasFile('signature_upload')) {
      $signaturePath = $request->file('signature_upload')->getRealPath();
    } elseif ($request->filled('signature_data')) {
      $imgData = base64_decode(str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->signature_data));
      $signaturePath = $tempDir . "/signature_bargo_{$approval_id}.png";
      file_put_contents($signaturePath, $imgData);
    }

    $centerX = 100;
    $signatureY = 155;
    $nameY = 164;

    if ($signaturePath && file_exists($signaturePath)) {
      $sigWidth = 40;
      list($ow, $oh) = getimagesize($signaturePath);
      $sigHeight = ($sigWidth / $ow) * $oh;
      $sigX = $centerX - ($sigWidth / 2);
      $pdf->Image($signaturePath, $sigX, $signatureY, $sigWidth, $sigHeight);
    }

    $pdf->SetFont('Helvetica', '', 10);
    $textWidth = $pdf->GetStringWidth($bargoName);
    $pdf->SetXY($centerX - ($textWidth / 2), $nameY);
    $pdf->Write(0, $bargoName);

    $outputPath = $tempDir . "/approved_bargo_{$permit->hashed_id}.pdf";
    $pdf->Output($outputPath, 'F');

    // Update Database
    $flow->update([
      'status' => 'approved',
      'approver_id' => Auth::user()->user_id,
      'approved_at' => now(),
    ]);

    // Save new PDF to permit
    $permit->update([
      'pdf_data' => file_get_contents($outputPath),
    ]);

    return back()->with('success', 'Permit approved and signed by BARGO.');
  }

  public function approved()
  {
    $approvedReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'BARGO')
      ->where('status', 'approved')
      ->orderBy('approved_at', 'desc')
      ->get();

    return view('bargo.events.approved', compact('approvedReviews'));
  }

  public function history()
  {
    $historyReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'BARGO')
      ->whereIn('status', ['approved', 'rejected'])
      ->orderBy('updated_at', 'desc')
      ->get();

    return view('bargo.events.history', compact('historyReviews'));
  }

  public function reject(Request $request, $approval_id)
  {
    $request->validate(['comments' => 'required|string']);

    $flow = EventApprovalFlow::findOrFail($approval_id);
    $flow->update([
      'status' => 'rejected',
      'comments' => $request->comments,
      'approver_id' => Auth::user()->user_id,
      'approved_at' => now(),
    ]);

    return back()->with('error', 'Permit rejected.');
  }
}
