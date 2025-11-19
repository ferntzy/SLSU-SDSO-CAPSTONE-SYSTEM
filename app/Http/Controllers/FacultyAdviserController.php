<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use App\Models\Organization;

class FacultyAdviserController extends Controller
{
  public function dashboard()
  {
    $adviserId = Auth::user()->user_id;

    $organizationIds = Organization::where('adviser_id', $adviserId)
      ->pluck('organization_id')
      ->toArray();

    $pendingReviews = $approved = $rejected = 0;

    if (!empty($organizationIds)) {
      $permitIds = Permit::whereIn('organization_id', $organizationIds)
        ->pluck('permit_id')
        ->toArray();

      $pendingReviews = EventApprovalFlow::where('approver_role', 'Faculty_Adviser')
        ->where('status', 'pending')
        ->whereIn('permit_id', $permitIds)
        ->count();

      $approved = EventApprovalFlow::where('approver_role', 'Faculty_Adviser')
        ->where('status', 'approved')
        ->whereIn('permit_id', $permitIds)
        ->count();

      $rejected = EventApprovalFlow::where('approver_role', 'Faculty_Adviser')
        ->where('status', 'rejected')
        ->whereIn('permit_id', $permitIds)
        ->count();
    }

    return view('adviser.dashboard', compact('pendingReviews', 'approved', 'rejected'));
  }

  public function approvals()
  {
    $adviserId = Auth::user()->user_id;

    $organizationIds = Organization::where('adviser_id', $adviserId)
      ->pluck('organization_id')
      ->toArray();

    $permitIds = Permit::whereIn('organization_id', $organizationIds)
      ->pluck('permit_id')
      ->toArray();

    $pendingPermits = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'Faculty_Adviser')
      ->where('status', 'pending')
      ->whereIn('permit_id', $permitIds)
      ->orderBy('created_at', 'desc')
      ->get();

    return view('adviser.approvals', compact('pendingPermits'));
  }

  public function viewPermitPdf($hashed_id)
  {
    $permit = Permit::where('hashed_id', $hashed_id)->first();

    if (!$permit) {
      abort(404, 'Permit not found.');
    }

    if (!$permit->pdf_data) {
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
      return back()->with('error', 'No PDF found for this permit.');
    }

    $adviserName = strtoupper(Auth::user()->name ?? 'UNKNOWN');

    $tempDir = storage_path('app/temp');
    if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

    $tempPdfPath = $tempDir . "/temp_permit_{$permit->hashed_id}.pdf";
    file_put_contents($tempPdfPath, $permit->pdf_data);

    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->setSourceFile($tempPdfPath);
    $templateId = $pdf->importPage(1);
    $pdf->useTemplate($templateId, 0, 0, 210);

    // ===== Signature =====
    $signaturePath = null;
    if ($request->hasFile('signature_upload')) {
      $signaturePath = $request->file('signature_upload')->getPathName();
    } elseif ($request->filled('signature_data')) {
      $imgData = str_replace('data:image/png;base64,', '', $request->input('signature_data'));
      $img = str_replace(' ', '+', $imgData);
      $signaturePath = $tempDir . "/signature_{$approval_id}.png";
      file_put_contents($signaturePath, base64_decode($img));
    }

    // ===== Coordinates =====
    $centerX = 100;
    $signatureY = 128;
    $nameY = 138;

    // Draw signature centered
    if ($signaturePath && file_exists($signaturePath)) {
      $sigWidth = 40;
      list($origWidth, $origHeight) = getimagesize($signaturePath);
      $sigHeight = ($sigWidth / $origWidth) * $origHeight;
      $sigX = $centerX - ($sigWidth / 2);
      $pdf->Image($signaturePath, $sigX, $signatureY, $sigWidth, $sigHeight);
    }

    // Draw name centered
    $pdf->SetFont('Helvetica', '', 10);
    $textWidth = $pdf->GetStringWidth($adviserName);
    $pdf->SetXY($centerX - ($textWidth / 2), $nameY);
    $pdf->Write(0, $adviserName);

    // ===== Save updated PDF to database =====
    $tempOutput = $tempDir . "/approved_{$permit->hashed_id}.pdf";
    $pdf->Output($tempOutput, 'F');
    $permit->pdf_data = file_get_contents($tempOutput);
    $permit->save();

    // Update approval flow
    $flow->update([
      'status' => 'approved',
      'approver_id' => Auth::user()->user_id,
      'approved_at' => now(),
    ]);

    return back()->with('success', 'Permit approved successfully!');
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

    return back()->with('success', 'Permit rejected successfully!');
  }

  public function viewTempPdf($hashed_id)
  {
    $filePath = storage_path("app/temp/approved_{$hashed_id}.pdf");
    if (!file_exists($filePath)) {
      abort(404, 'Temporary PDF not found.');
    }

    return response()->file($filePath);
  }
}
