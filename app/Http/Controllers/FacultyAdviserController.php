<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use App\Models\Organization;
use Illuminate\Support\Facades\Hash;
use setasign\Fpdi\Fpdi;



class FacultyAdviserController extends Controller
{
  // Helper to get all organizations advised by current user
  private function getAdvisedOrganizationIds()
  {
    return Organization::where('adviser_id', Auth::id())
      ->pluck('organization_id')
      ->toArray();
  }

  public function dashboard()
  {
    $organizationIds = $this->getAdvisedOrganizationIds();

    $pendingReviews = $approved = $rejected = 0;

    if (!empty($organizationIds)) {
      $permitIds = Permit::whereIn('organization_id', $organizationIds)
        ->pluck('permit_id');

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
public function approvalHistory()
{
    $advisedOrgIds = $this->getAdvisedOrganizationIds();

    $history = EventApprovalFlow::with(['permit.organization'])
        ->where('approver_role', 'Faculty_Adviser')
        ->whereIn('permit_id', function ($query) use ($advisedOrgIds) {
            $query->select('permit_id')
                  ->from('permits')
                  ->whereIn('organization_id', $advisedOrgIds);
        })
        ->whereIn('status', ['approved'])
        ->latest('updated_at')
        ->paginate(15);

    return view('adviser.history', compact('history'));
}
  public function approvals()
  {
    $organizationIds = $this->getAdvisedOrganizationIds();

    $permitIds = Permit::whereIn('organization_id', $organizationIds)
      ->pluck('permit_id');

    $pendingPermits = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'Faculty_Adviser')
      ->where('status', 'pending')
      ->orderBy('created_at', 'asc')
      ->whereIn('permit_id', $permitIds)
      ->latest('created_at')
      ->get();

    return view('adviser.approvals', compact('pendingPermits'));
  }

  // Your existing approve(), reject(), viewPermitPdf(), etc. methods
  // → NO CHANGES NEEDED! They still work perfectly.

  public function viewPermitPdf($hashed_id)
  {
    $permit = Permit::where('hashed_id', $hashed_id)->firstOrFail();

    if (!$permit->pdf_data) {
      abort(404, 'PDF not generated yet.');
    }

    return response($permit->pdf_data, 200)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="permit_' . $hashed_id . '.pdf"');
  }

  public function approve($approval_id, Request $request)
  {
    $flow = EventApprovalFlow::findOrFail($approval_id);

    // Verify role
    if ($flow->approver_role !== 'Faculty_Adviser') {
      abort(403, 'Unauthorized');
    }

    // Confirm password


    // Determine signature path
    $signaturePath = null;
    if (Auth::user()->signature && file_exists(storage_path('app/public/' . Auth::user()->signature))) {
      $signaturePath = storage_path('app/public/' . Auth::user()->signature);
    } elseif ($request->filled('signature_data')) {
      $data = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->signature_data);
      $signaturePath = storage_path('app/temp_sig_' . Auth::id() . '.png');
      file_put_contents($signaturePath, base64_decode($data));
    }

    // Get full name
    $fullName = Auth::user()->user_profile
      ? trim(
        Auth::user()->user_profile->first_name . ' ' .
          (Auth::user()->user_profile->middle_name ? strtoupper(substr(Auth::user()->user_profile->middle_name, 0, 1)) . '.' : '') . ' ' .
          Auth::user()->user_profile->last_name . ' ' .
          (Auth::user()->user_profile->suffix ?? '')
      )
      : Auth::user()->username;

    // Update approval flow
    $flow->status = 'approved';
    $flow->approver_name = $fullName;
    $flow->approver_id = Auth::id();
    $flow->updated_at = now();
    $flow->save();

    // Embed signature and name into PDF
    $permit = $flow->permit;
    if ($permit && $permit->pdf_data) {
      $tempDir = storage_path('app/temp');
      if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

      $tempPdfPath = $tempDir . '/permit_' . $permit->permit_id . '.pdf';
      file_put_contents($tempPdfPath, $permit->pdf_data);

      $pdf = new Fpdi();
      $pageCount = $pdf->setSourceFile($tempPdfPath);

      for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplIdx = $pdf->importPage($pageNo);
        $pdf->AddPage();
        $pdf->useTemplate($tplIdx);

        // Only draw on first page
        if ($pageNo === 1 && $signaturePath && file_exists($signaturePath)) {
          $centerX = 100;
          $signatureY = 120;
          $nameY = 138;

          // Signature
          $sigWidth = 40;
          list($origWidth, $origHeight) = getimagesize($signaturePath);
          $sigHeight = ($sigWidth / $origWidth) * $origHeight;
          $sigX = $centerX - ($sigWidth / 2);
          $pdf->Image($signaturePath, $sigX, $signatureY, $sigWidth, $sigHeight);

          // Name
          $pdf->SetFont('Helvetica', '', 11);
          $fullNameUpper = strtoupper($fullName);
          $textWidth = $pdf->GetStringWidth($fullNameUpper);
          $pdf->SetFont('Helvetica', '', 10);
          $pdf->SetXY($centerX - ($textWidth / 2), $nameY);
          $pdf->Write(0, $fullNameUpper);
        }
      }

      // Save updated PDF to DB
      $tempOutput = $tempDir . "/approved_{$permit->hashed_id}.pdf";
      $pdf->Output($tempOutput, 'F');
      $permit->pdf_data = file_get_contents($tempOutput);
      $permit->save();

      @unlink($tempPdfPath);
      if ($signaturePath && str_contains($signaturePath, 'temp_sig_')) {
        @unlink($signaturePath);
      }
    }



    return back()->with('success', 'Permit approved and signed successfully.');
  }
  public function reject($approval_id, Request $request)
  {
    // Validate input
    $request->validate([
      'comments' => 'required|string|max:1000',
    ]);

    // Find the approval flow record
    $flow = EventApprovalFlow::findOrFail($approval_id);

    // Verify role
    if ($flow->approver_role !== 'Faculty_Adviser') {
      abort(403, 'Unauthorized');
    }

    // Get the adviser's full name
    $fullName = Auth::user()->user_profile
      ? trim(
        Auth::user()->user_profile->first_name . ' ' .
          (Auth::user()->user_profile->middle_name ? strtoupper(substr(Auth::user()->user_profile->middle_name, 0, 1)) . '.' : '') . ' ' .
          Auth::user()->user_profile->last_name . ' ' .
          (Auth::user()->user_profile->suffix ?? '')
      )
      : Auth::user()->username;

    // Update the approval flow
    $flow->status = 'rejected';
    $flow->comments = $request->comments;
    $flow->approver_id = Auth::id();
    $flow->approver_name = $fullName; // optional, if you have this column
    $flow->updated_at = now();
    $flow->save();

    return back()->with('success', 'Permit rejected successfully.');
  }
}
