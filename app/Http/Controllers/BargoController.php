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
class BargoController extends Controller
{
  public function dashboard()
  {
    $pendingReviews = EventApprovalFlow::where('approver_role', 'BARGO')
      ->where('status', 'pending')
      ->count();

    $approved = EventApprovalFlow::where('approver_role', 'VP_SAS')
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
      ->oldest('created_at')
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
  public function profile()
  {
    return view('bargo.profile');
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
    return view('bargo.calendar');
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
  public function storeEvent(Request $request)
  {
    $orgId = $this->getUserOrganizationId();
    // $organization = Organization::findOrFail($orgId);
    $permit = Permit::create([
    'organization_id' => $orgId,
    'title_activity'  => $request->title_activity,
    'purpose'         => $request->purpose,
    'type'            => $request->type,
    'nature'          => $request->nature === 'Other' ? $request->nature_other_text : $request->nature,
    'venue'           => $request->venue,
    'date_start'      => $request->date_start,
    'date_end'        => $request->date_end ?: $request->date_start,
    'time_start'      => Carbon::createFromFormat('h:i A', $request->time_start)->format('H:i:s'),
    'time_end'        => Carbon::createFromFormat('h:i A', $request->time_end)->format('H:i:s'),
    'participants'    => $request->participants === 'Other' ? $request->participants_other_text : $request->participants,
    'number'          => $request->number,
]);
$stages = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
    foreach ($stages as $stage) {
      EventApprovalFlow::create([
        'permit_id' => $permit->permit_id,
        'approver_role' => $stage,
        'status' => 'pending',
      ]);
    }
    return response()->json([
      'success' => true,
      'message' => 'Event added to calendar!',
      'event' => $event
    ]);
  }

  public function updateEvent(Request $request, $event_id)
  {
    $event = \App\Models\Event::where('event_id', $event_id)
      ->where('organization_id', Auth::user()->organization_id)
      ->firstOrFail();

    $request->validate([
      'title' => 'required|string|max:255',
      'start_date' => 'required|date',
      'venue_id' => 'nullable|exists:venues,venue_id',
      'description' => 'nullable|string',
    ]);

    $event->update([
      'event_title' => $request->title,
      'event_date' => $request->start_date,
      'venue_id' => $request->venue_id,
      'description' => $request->description ?? '',
    ]);

    return response()->json(['success' => true, 'message' => 'Event updated!']);
  }

  public function deleteEvent($event_id)
  {
    $event = \App\Models\Event::where('event_id', $event_id)
      ->where('organization_id', Auth::user()->organization_id)
      ->firstOrFail();

    $event->delete();

    return response()->json(['success' => true, 'message' => 'Event deleted!']);
  }
public function storeBargoEvent(Request $request)
{
   $orgId = $this->getUserOrganizationId();

    $request->validate([
        'title_activity' => 'required|string|max:255',
        'purpose'        => 'required|string',
        'nature'         => 'required|string',
        'nature_other_text' => 'nullable|required_if:nature,Other|string|max:255',
        'venue'          => 'required|exists:venues,venue_id',
        'participants'   => 'required|string',
        'participants_other_text' => 'nullable|required_if:participants,Other|string',
        'number'         => 'nullable|integer|min:1',
        'date_start'     => 'required|date',
        'date_end'       => 'nullable|date|after_or_equal:date_start',
    ]);

    // Create Permit — Always In-Campus
    $permit = Permit::create([
        'organization_id' => $orgId,
        'title_activity'  => $request->title_activity,
        'purpose'         => $request->purpose,
        'type'            => 'In-Campus',
        'nature'          => $request->nature === 'Other' ? $request->nature_other_text : $request->nature,
        'venue'           => $request->venue,
        'date_start'      => $request->date_start,
        'date_end'        => $request->date_end ?? $request->date_start,
        'time_start'      => null,
        'time_end'        => null,
        'participants'    => $request->participants === 'Other' ? $request->participants_other_text : $request->participants,
        'number'          => $request('number', 0),
        'is_completed'    => 1,
        'completed_at'    => now(),
    ]);

    // Auto-approve by VP_SAS (BARGO authority)
    $user = Auth::user();
    $fullName = trim(($user->user_profile?->first_name ?? '') . ' ' .
        ($user->user_profile?->last_name ?? '')) ?: $user->username;

    EventApprovalFlow::create([
        'permit_id'       => $permit->permit_id,
        'approver_role'   => 'VP_SAS',
        'approver_id'     => $user->user_id,
        'approver_name'   => $fullName,
        'status'          => 'approved',
        'approved_at'     => now(),
        'created_at'      => now(),
        'updated_at'      => now(),
    ]);

    // Create Event in events table (for calendar display)
    $event = \App\Models\Event::create([
        'organization_id' => $orgId,
        'event_title'     => $request->title_activity,
        'event_date'      => $request->date_start,
        'venue_id'        => $request->venue,
        'proposal_status' => 'approved',
        'current_stage'   => 'completed',
        'event_report_submitted' => 1,
        'event_permit_submitted' => 1,
    ]);

    return response()->json([
        'success' => true,
        'message' => 'BARGO event created and approved instantly!',
        'permit_id' => $permit->permit_id
    ]);
}
public function updateBargoEvent(Request $request, Event $event)
{
    // Only allow BARGO to edit their own auto-approved events
    if ($event->proposal_status !== 'approved' || $event->current_stage !== 'completed') {
        return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
    }

    $request->validate([
        'title'       => 'required|string|max:255',
        'event_date'  => 'required|date',
        'venue'       => 'nullable|string',
        'description' => 'nullable|string',
    ]);

    $event->update([
        'event_title' => $request->title,
        'event_date'  => $request->event_date,
        'description' => $request->description,
    ]);

    return response()->json(['success' => true, 'message' => 'Event updated!']);
}

public function deleteBargoEvent(Event $event)
{
    if ($event->proposal_status !== 'approved' || $event->current_stage !== 'completed') {
        return response()->json(['success' => false, 'message' => 'Not authorized'], 403);
    }

    $event->delete();

    return response()->json(['success' => true, 'message' => 'Event deleted!']);
}
}
