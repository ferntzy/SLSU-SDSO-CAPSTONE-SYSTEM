<?php

namespace App\Http\Controllers;
use App\Models\Event;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Storage;
use App\Models\BargoEvent;
use Carbon\Carbon;
use App\Models\Organization;
class SdsoheadController extends Controller
{
  public function dashboard()
  {
   $pendingReviews = EventApprovalFlow::where('approver_role', 'SDSO_HEAD')
    ->where('status', 'pending')
    ->whereHas('permit.approvalFlow', fn($q) =>
        $q->where('approver_role', 'BARGO')
          ->where('status', 'approved')
    )
    ->count();

    $approved = EventApprovalFlow::where('approver_role', 'SDSO_HEAD')
      ->where('status', 'approved')
      ->count();

    $rejected = EventApprovalFlow::where('approver_role', 'SDSO_Head')
      ->where('status', 'rejected')
      ->count();

    return view('sdso.dashboard', compact('pendingReviews', 'approved', 'rejected'));
  }

  public function pending()
  {
  $pendingReviews = EventApprovalFlow::with(['permit.organization'])
    ->where('approver_role', 'SDSO_Head')
    ->where('status', 'pending')

    // 1. Faculty Adviser must have approved
    ->whereHas('permit.approvalFlow', function ($q) {
        $q->where('approver_role', 'BARGO')
          ->where('status', 'approved');
    })

    // 2. SAS_Director must be PENDING (not approved, not rejected)
    ->whereHas('permit.approvalFlow', function ($q) {
        $q->where('approver_role', 'SAS_Director')
          ->where('status', 'pending');
    })

    ->oldest('created_at')
    ->get();

    return view('sdso.events.pending', compact('pendingReviews'));
  }

  public function approved()
  {
    $approvedReviews = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->where('status', 'approved')
      ->latest('approved_at')
      ->get();

    return view('sdso.events.approved', compact('approvedReviews'));
  }

  public function rejected()
  {
    $rejectedReviews = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->where('status', 'rejected')
      ->latest('updated_at')
      ->get();

    return view('sdso.events.rejected', compact('rejectedReviews'));
  }

  public function history()
  {
    $historyReviews = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->whereIn('status', ['approved', 'rejected'])
      ->latest('updated_at')
      ->get();

    return view('sdso.events.history', compact('historyReviews'));
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

    // Security check
    if ($flow->approver_role !== 'SDSO_Head' || $flow->status !== 'pending') {
        return response()->json([
            'success' => false,
            'message' => 'Unauthorized or already processed.'
        ], 403);
    }

    $permit = $flow->permit;
    if (!$permit || !$permit->pdf_data) {
        return response()->json([
            'success' => false,
            'message' => 'Permit PDF not found.'
        ], 400);
    }

    $user = Auth::user();

    // === 1. GET YOUR REAL UPLOADED SIGNATURE ===
    if (!$user->signature || !Storage::disk('public')->exists($user->signature)) {
        return response()->json([
            'success' => false,
            'message' => 'Please upload your signature in your profile first.'
        ], 400);
    }

    $signaturePath = storage_path('app/public/' . $user->signature);

    // === 2. GET FULL NAME (SAME AS YOUR CODE) ===
    $fullName = $user->user_profile
        ? trim(
            $user->user_profile->first_name . ' ' .
            ($user->user_profile->middle_name ? strtoupper(substr($user->user_profile->middle_name, 0, 1)) . '.' : '') . ' ' .
            $user->user_profile->last_name . ' ' .
            ($user->user_profile->suffix ?? '')
        )
        : $user->username;

    $fullNameUpper = strtoupper($fullName);

    // === 3. UPDATE APPROVAL FLOW FIRST ===
    $flow->update([
        'status'        => 'approved',
        'approver_id'   => $user->user_id,
        'approver_name' => $fullNameUpper,
        'signature_path'=> $user->signature,
        'approved_at'   => now(),
    ]);

    // === 4. SIGN THE PDF — YOUR STYLE, BUT MOVED 26 UNITS RIGHT + NOT BOLD ===
    $tempDir = storage_path('app/temp');
    if (!is_dir($tempDir)) mkdir($tempDir, 0755, true);

    $tempPdfPath = $tempDir . "/permit_{$permit->hashed_id}.pdf";
    file_put_contents($tempPdfPath, $permit->pdf_data);

    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($tempPdfPath);

    for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
        $tplIdx = $pdf->importPage($pageNo);
        $size = $pdf->getTemplateSize($tplIdx);
        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplIdx);

        // SIGN ONLY ON FIRST PAGE
        if ($pageNo === 1 && file_exists($signaturePath)) {
            // MOVED 26 UNITS TO THE RIGHT → X = 126 (100 + 26)
            $centerX     = 47;
            $signatureY  = 155;
            $nameY       = 171;

            // Signature
            $sigWidth = 40;
            list($origW, $origH) = getimagesize($signaturePath);
            $sigHeight = ($sigWidth / $origW) * $origH;
            $sigX = $centerX - ($sigWidth / 2);

            $pdf->Image($signaturePath, $sigX, $signatureY, $sigWidth, $sigHeight);

            // Name — CAPITALIZED, NOT BOLD, REGULAR FONT
            $pdf->SetFont('Helvetica', '', 10);  // ← '' = Regular (not 'B')
            $pdf->SetTextColor(0, 0, 0);

            $textWidth = $pdf->GetStringWidth($fullNameUpper);
            $pdf->SetXY($centerX - ($textWidth / 2), $nameY);
            $pdf->Write(0, $fullNameUpper);
        }
    }

    // Save signed PDF
    $signedPath = $tempDir . "/sdso_signed_{$permit->hashed_id}.pdf";
    $pdf->Output($signedPath, 'F');

    // Update permit with signed version
    $permit->pdf_data = file_get_contents($signedPath);
    $permit->save();

    // Cleanup
    @unlink($tempPdfPath);
    @unlink($signedPath);

    return response()->json([
        'success' => true,
        'message' => 'Permit successfully approved and signed by SDS.'
    ]);
      // return response($permit->pdf_data, 200)
      // ->header('Content-Type', 'application/pdf')
      // ->header('Content-Disposition', 'inline; filename="Permit_' . $hashed_id . '.pdf"');

}

  // BARGO REJECT
  public function reject(Request $request, $approval_id)
  {
    $request->validate([
      'comments' => 'required|string|max:1000'
    ]);

    $flow = EventApprovalFlow::findOrFail($approval_id);

    if ($flow->approver_role !== 'SDSO_Head' || $flow->status !== 'pending') {
      abort(403);
    }

    $flow->update([
      'status' => 'rejected',
      'comments' => $request->comments,
      'approver_id' => Auth::id(),
      'approver_name' => strtoupper(Auth::user()->name ?? 'SDSO_Head'),
      'updated_at' => now(),
    ]);

    return back()->with('error', 'Permit has been rejected.');
  }
  public function profile()
  {
    return view('sdso.profile');
  }

  public function uploadSignature(Request $request)
  {
    $request->validate([
      'signature_data'   => 'required_without:signature_upload|string',
      'signature_upload' => 'required_without:signature_data|image|mimes:png,jpg,jpeg|max:2048',
    ]);

    $user = Auth::user();

    // Delete old signature
    if ($user->signature && Storage::disk('public')->exists($user->signature)) {
      Storage::disk('public')->delete($user->signature);
    }

    $path = null;

    if ($request->filled('signature_data')) {
      $data = preg_replace('#^data:image/\w+;base64,#i', '', $request->signature_data);
      $data = str_replace(' ', '+', $data);
      $imageData = base64_decode($data);

      $filename = 'signatures/signature_' . $user->user_id . '_' . time() . '.png';
      Storage::disk('public')->put($filename, $imageData);
      $path = $filename;
    } elseif ($request->hasFile('signature_upload')) {
      $path = $request->file('signature_upload')->store('signatures', 'public');
    }

    $user->update(['signature' => $path]);

    return response()->json([
      'success' => true,
      'message' => 'Signature saved!',
      'signature_url' => asset('storage/' . $path)
    ]);
  }

  public function removeSignature()
  {
    $user = Auth::user();
    if ($user->signature) {
      Storage::disk('public')->delete($user->signature);
      $user->update(['signature' => null]);
    }
    return back()->with('success', 'Signature removed.');
  }

  public function updateProfile(Request $request)
  {
    $request->validate([
      'first_name' => 'required|string|max:255',
      'last_name'  => 'required|string|max:255',
      'contact_number' => 'nullable|string|max:20',
    ]);

    $profile = $user->profile;
    $profile?->update($request->only(['first_name', 'last_name', 'contact_number']));

    return back()->with('success', 'Profile updated!');
  }
  // app/Http/Controllers/BargoController.php

  public function calendar()
  {
    return view('sdso.calendar');
  }

 public function getEvents()
{
    $bargoUserId = Auth::id();

    $events = Event::with('organization', 'venue')->get();

    return response()->json($events->map(function ($event) use ($bargoUserId) {
        $isBargoEvent = $event->event_report_submitted == 1; // or your own flag

        return [
            'id' => $event->event_id,
            'title' => $event->event_title,
            'start' => $event->event_date,
            'allDay' => true,
            'backgroundColor' => $isBargoEvent ? '#ff851b' : '#28c76f',
            'borderColor' => $isBargoEvent ? '#ff851b' : '#28c76f',
            'extendedProps' => [
                'venue' => $event->venue?->venue_name ?? 'TBA',
                'organization_name' => $event->organization?->organization_name ?? 'Unknown',
                'is_bargo_event' => $isBargoEvent
            ]
        ];
    }));
}

}
