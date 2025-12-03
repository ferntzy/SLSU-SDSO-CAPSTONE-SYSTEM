<?php

namespace App\Http\Controllers;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use App\Models\Permit;
use App\Models\Organization;
use App\Models\Venue;
use App\Models\OffCampusRequirement;
use App\Models\EventApprovalFlow;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use setasign\Fpdi\Fpdi;
use Carbon\Carbon;
class PermitController extends Controller
{
  private function getUserOrganizationId()
  {
    $user = Auth::user();
    $profileId = DB::table('users')->where('user_id', $user->user_id)->value('profile_id');

    if (!$profileId) abort(403, 'No profile linked.');

    $member = DB::table('members')->where('profile_id', $profileId)->first();
    if (!$member) abort(403, 'You are not in any organization.');

    return $member->organization_id;
  }

  public function showForm(Request $request)
  {
    $orgId = $this->getUserOrganizationId();
    $organization = Organization::findOrFail($orgId);

    $dateStart = $request->query('date_start');
    $dateEnd = $request->query('date_end');
    if ($dateStart && !$dateEnd) $dateEnd = $dateStart;

    $venues = Venue::orderBy('venue_name', 'asc')->get();

    return view('student.permit.form', compact('venues', 'dateStart', 'dateEnd', 'organization'));
  }

  public function generate(Request $request)
  {
    $orgId = $this->getUserOrganizationId();
    $organization = Organization::findOrFail($orgId);

    $request->validate([
        'title_activity' => 'required|string|max:255',
        'purpose' => 'required|string',
        'type' => 'required|in:In-Campus,Off-Campus',
        'nature' => 'required|string',
        'nature_other_text' => 'nullable|required_if:nature,Other',
        'venue' => 'required|string|max:255',
        'date_start' => 'required|date',
        'date_end' => 'nullable|date|after_or_equal:date_start',
        'time_start' => 'required',
        'time_end' => 'required',
        'participants' => 'required|string',
        'participants_other_text' => 'nullable|required_if:participants,Other',
        'number' => 'required|integer|min:1',
    ]);

    // Create Permit
   // ← Make sure this is at the top of your controller file

// ...

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

    // Create Approval Flow
    $stages = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
    foreach ($stages as $stage) {
      EventApprovalFlow::create([
        'permit_id' => $permit->permit_id,
        'approver_role' => $stage,
        'status' => 'pending',
      ]);
    }

    // FIXED: Event now uses correct organization_id → organizations table
    $event = \App\Models\Event::create([
      'organization_id' => $orgId,  // ← This is now correct
      'event_title' => $request->title_activity,
      'event_date' => $request->date_start,
      'proposal_status' => 'pending',
    ]);

    foreach ($stages as $role) {
      \App\Models\EventApproval::create([
        'event_id' => $event->event_id,
        'approver_role' => $role,
        'status' => 'pending',
      ]);
    }

    // NOTIFICATION TO FACULTY ADVISER (THIS IS THE ONLY NEW PART)
    $adviserUserId = $organization->adviser?->user_id;

    if ($adviserUserId) {
        DB::table('notifications')->insert([
            'user_id'           => $adviserUserId,
            'message'           => $organization->organization_name . ' submitted a new permit: ' . $permit->title_activity,
            'notification_type' => 'event_approval',
            'status'            => 'unread',
            'created_at'        => now(),
            'updated_at'        => now(),
        ]);
    }

    // PDF Generation — YOUR EXACT COORDINATES PRESERVED
    $templatePath = public_path('templates/sdso_org_permit.pdf');
    if (!file_exists($templatePath)) {
      return back()->withErrors(['pdf' => 'Template not found.']);
    }

    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId, 0, 0, 210);

    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);

    $fullName = Auth::user()->user_profile
      ? trim(Auth::user()->user_profile->first_name . ' ' .
        (Auth::user()->user_profile->middle_name ? strtoupper(substr(Auth::user()->user_profile->middle_name, 0, 1)) . '.' : '') . ' ' .
        Auth::user()->user_profile->last_name . ' ' .
        (Auth::user()->user_profile->suffix ?? ''))
      : Auth::user()->username;

    // YOUR ORIGINAL COORDINATES — UNCHANGED
    $pdf->SetXY(73.5, 41);
    $pdf->Write(0, $fullName);
    $pdf->SetXY(73.5, 45);
    $pdf->Write(0, $organization->organization_name);
    $pdf->SetXY(73.5, 49);
    $pdf->Write(0, $request->title_activity);
    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetXY(73.5, 51);
    $pdf->MultiCell(140, 5, $request->purpose);
    $pdf->SetFont('Helvetica', '', 10);

    // TYPE
    $pdf->SetFont('ZapfDingbats', '', 12);
    $pdf->SetXY($request->type === 'Off-Campus' ? 124.3 : 75, 62);
    $pdf->Write(0, chr(52));

    // NATURE — your exact positions
    $naturePositions = [
      'Training/Seminar' => [75, 70],
      'Conference/Summit' => [124.3, 70],
      'Culmination' => [75, 74.3],
      'Socialization' => [124.3, 74.3],
      'Meeting' => [75, 78.3],
      'Concert' => [124.3, 78.3],
      'Exhibit' => [75, 82.6],
      'Program' => [124.3, 82.6],
      'Educational Tour' => [75, 86.9],
      'Clean and Green' => [124.3, 86.9],
      'Competition' => [75, 91.2],
      'Other' => [124.3, 91.2],
    ];
    $key = $request->nature === 'Other' ? 'Other' : $request->nature;
    if (isset($naturePositions[$key])) {
      [$x, $y] = $naturePositions[$key];
      $pdf->SetXY($x, $y);
      $pdf->Write(0, chr(52));
    }
    if ($request->nature === 'Other' && $request->nature_other_text) {
      $pdf->SetFont('Helvetica', '', 12);
      $pdf->SetXY(138, 91.2);
      $pdf->Write(0, $request->nature_other_text);
    }

    // DATE & TIME
    $startDate = strtotime($request->date_start);
    $endDate = $request->date_end ? strtotime($request->date_end) : $startDate;
    $dateDisplay = ($endDate !== $startDate && date('m/Y', $startDate) === date('m/Y', $endDate))
      ? date('m/d', $startDate) . '-' . date('d/Y', $endDate)
      : ($endDate !== $startDate ? date('m/d/Y', $startDate) . ' - ' . date('m/d/Y', $endDate) : date('m/d/Y', $startDate));

    $timeDisplay = date("g:i A", strtotime($request->time_start)) . ' - ' . date("g:i A", strtotime($request->time_end));

    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetXY(73.5, 95.5);
    $pdf->Write(0, $request->venue);
    $pdf->SetXY(73.5, 99.6);
    $pdf->Write(0, $dateDisplay);
    $pdf->SetXY(142, 99.6);
    $pdf->Write(0, $timeDisplay);

    // PARTICIPANTS
    $pdf->SetFont('ZapfDingbats', '', 12);
    $partPos = ['Members' => [75, 103.5], 'Officers' => [75, 107.7], 'All Students' => [75, 111.8], 'Other' => [75, 116]];
    $pKey = $request->participants === 'Other' ? 'Other' : $request->participants;
    if (isset($partPos[$pKey])) {
      [$x, $y] = $partPos[$pKey];
      $pdf->SetXY($x, $y);
      $pdf->Write(0, chr(52));
    }
    if ($request->participants === 'Other' && $request->participants_other_text) {
      $pdf->SetFont('Helvetica', '', 11);
      $pdf->SetXY(90, 116);
      $pdf->Write(0, $request->participants_other_text);
    }

    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetXY(142, 110);
    $pdf->Write(0, $request->number);

    // NAME & SIGNATURE — your exact logic
    $text = strtoupper($fullName);
    $w = $pdf->GetStringWidth($text);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetXY(47 - ($w / 2), 138);
    $pdf->Write(0, $text);
    $pdf->SetXY(153 - ($w / 2), 223);
    $pdf->Write(0, $text);

    // Signature
    $sigPath = null;
    if (Auth::user()->signature && file_exists(storage_path('app/public/' . Auth::user()->signature))) {
      $sigPath = storage_path('app/public/' . Auth::user()->signature);
    } elseif ($request->filled('signature_data')) {
      $data = str_replace(['data:image/png;base64,', ' '], ['', '+'], $request->signature_data);
      $sigPath = storage_path('app/temp_sig_' . Auth::id() . '.png');
      file_put_contents($sigPath, base64_decode($data));
    }

    if ($sigPath && file_exists($sigPath)) {
      $pdf->Image($sigPath, 27, 120, 40, 20);
      $pdf->Image($sigPath, 133, 207, 40, 20);
    }
    if ($sigPath && str_contains($sigPath, 'temp_sig_')) @unlink($sigPath);

    // Save PDF
    $pdfData = $pdf->Output('S');
    $permit->update(['pdf_data' => $pdfData]);

    // Off-Campus Files
    if ($request->type === 'Off-Campus' && $request->has('requirements')) {
      foreach ($request->requirements as $type) {
        $key = "requirement_files.{$type}";
        if ($request->hasFile($key)) {
          $file = $request->file($key);
          $name = "{$type}_{$permit->permit_id}_" . time() . '.' . $file->extension();
          $path = $file->storeAs("permits/offcampus/{$permit->permit_id}", $name, 'public');
          OffCampusRequirement::create([
            'permit_id' => $permit->permit_id,
            'requirement_type' => $type,
            'file_path' => $path,
            'original_filename' => $file->getClientOriginalName(),
          ]);
        }
      }
    }

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="permit_' . $permit->permit_id . '.pdf"');
  }

  public function track()
  {
    $orgId = $this->getUserOrganizationId();

    $permits = Permit::with('approvalFlow')
      ->where('organization_id', $orgId)
      ->latest()
      ->get();

    return view('student.permit.tracking', compact('permits'));
  }
 public function download($hashedId)
    {
        try {
            // Find the permit by hashed_id
            $permit = Permit::where('hashed_id', $hashedId)->firstOrFail();

            // Get the current user
            $user = Auth::user();

            // Check if user has access to this permit through organization membership
            $hasAccess = \DB::table('permits')
                ->join('organizations', 'permits.organization_id', '=', 'organizations.organization_id')
                ->join('members', 'organizations.organization_id', '=', 'members.organization_id')
                ->join('user_profiles', 'members.profile_id', '=', 'user_profiles.profile_id')
                ->join('users', 'user_profiles.profile_id', '=', 'users.profile_id')
                ->where('permits.hashed_id', $hashedId)
                ->where('users.user_id', $user->user_id)
                ->exists();

            if (!$hasAccess) {
                abort(403, 'Unauthorized access to this permit.');
            }

            // Check if permit is fully approved
            if (!$permit->isFullyApproved()) {
                return redirect()->back()->with('error', 'This permit is not yet fully approved.');
            }

            // Check if PDF data exists in database
            if ($permit->pdf_data) {
                // Return the stored PDF from database
                return response($permit->pdf_data)
                    ->header('Content-Type', 'application/pdf')
                    ->header('Content-Disposition', 'attachment; filename="Permit-' . $permit->hashed_id . '.pdf"');
            }

            // If no PDF in database, generate one
            $pdf = $this->generatePermitPDF($permit);

            // Download the PDF
            return $pdf->download('Permit-' . $permit->hashed_id . '.pdf');

        } catch (\Exception $e) {
            \Log::error('Permit download error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Unable to download permit. Please try again.');
        }
    }
      private function generatePermitPDF($permit)
    {
        // Get organization details
        $organization = $permit->organization;

        // Get all approvals with signatures
        $approvals = $permit->approvals()
            ->where('status', 'approved')
            ->with('approver')
            ->orderBy('approved_at')
            ->get();

        // Generate PDF
        $pdf = PDF::loadView('student.permit.pdf', [
            'permit' => $permit,
            'organization' => $organization,
            'approvals' => $approvals
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf;
    }
  public function viewPdf($id)
  {
    $permit = Permit::findOrFail($id);
    if (!$permit->pdf_data) abort(404);
    return response($permit->pdf_data)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="permit.pdf"');
  }
}
