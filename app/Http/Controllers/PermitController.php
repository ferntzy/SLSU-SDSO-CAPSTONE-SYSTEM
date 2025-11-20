<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\EventApproval;
use App\Models\EventApprovalFlow;
use App\Models\Organization;
use App\Models\Permit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use setasign\Fpdi\Fpdi;
use Vinkla\Hashids\Facades\Hashids;


class PermitController extends Controller
{
  public function showForm()
  {
    $user = auth()->user();
    $organizations = Organization::where('user_id', $user->user_id)->get();
    return view('student.permit.form', compact('organizations'));
  }

  public function showCalendar()
{
      $user = auth()->user();
    $organizations = Organization::where('user_id', $user->user_id)->get();
    return view('student.calendardisplay', compact('organizations'));
}


  public function generate(Request $request)
  {
    $request->validate([
      'name' => 'required|string|max:255',
      'organization_id' => 'required|exists:organizations,organization_id',
      'title_activity' => 'required|string|max:255',
      'purpose' => 'nullable|string',
      'venue' => 'nullable|string|max:255',
      'date_start' => 'required|date',
      'date_end' => 'nullable|date|after_or_equal:date_start',
      'time_start' => 'required',
      'time_end' => 'required',
      'number' => 'nullable|integer',
      'type' => 'nullable|string',
      'nature' => 'nullable|string',
      'participants' => 'nullable|string',
      'participants_other' => 'nullable|string',
      'signature_upload' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
      'signature_data' => 'nullable|string'
    ]);

    $timeStart = date('H:i:s', strtotime($request->time_start));
    $timeEnd = date('H:i:s', strtotime($request->time_end));

    $permit = Permit::create([
      'organization_id' => $request->organization_id,
      'title_activity' => $request->title_activity,
      'purpose' => $request->purpose,
      'type' => $request->type,
      'nature' => $request->nature,
      'venue' => $request->venue,
      'date_start' => $request->date_start,
      'date_end' => $request->date_end,
      'time_start' => $timeStart,
      'time_end' => $timeEnd,
      'participants' => $request->participants,
      'number' => $request->number,
      'signature_data' => $request->signature_data ?? null,
    ]);

    $stages = [
      'Faculty_Adviser',
      'BARGO',
      'SDSO_Head',
      'SAS_Director',
      'VP_SAS'
    ];

    foreach ($stages as $stage) {
      EventApprovalFlow::create([
        'permit_id' => $permit->permit_id,
        'approver_role' => $stage,
        'status' => 'pending',
      ]);
    }
    $event = Event::create([
      'organization_id' => $request->organization_id,
      'event_title' => $request->title_activity,
      'event_date' => $request->date_start,
      'proposal_status' => 'pending',
    ]);

    $roles = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
    foreach ($roles as $role) {
      EventApproval::create([
        'event_id' => $event->event_id,
        'approver_role' => $role,
        'status' => 'pending',
      ]);
    }

    $organization = Organization::find($request->organization_id);
    $organizationName = $organization ? $organization->organization_name : 'N/A';

    $templatePath = public_path('templates/sdso_org_permit.pdf');
    if (!file_exists($templatePath)) {
      return back()->withErrors(['pdf' => 'Permit template file not found.']);
    }

    $pdf = new Fpdi();
    $pdf->AddPage();
    $pdf->setSourceFile($templatePath);
    $tplId = $pdf->importPage(1);
    $pdf->useTemplate($tplId, 0, 0, 210);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->SetTextColor(0, 0, 0);

    // 🖋 Basic Info (only name centered)
    $pdf->SetXY(73.5, 41);
    $pdf->Write(0, $request->name);  // Centered
    $pdf->SetXY(73.5, 45);
    $pdf->Write(0, $organizationName);
    $pdf->SetXY(73.5, 49);
    $pdf->Write(0, $request->title_activity);

    $pdf->SetFont('Helvetica', '', 8);
    $pdf->SetXY(73.5, 51);
    $pdf->MultiCell(140, 5, $request->purpose);
    $pdf->SetFont('Helvetica', '', 10);

    // 🖋 Type Checkboxes
    $pdf->SetFont('ZapfDingbats', '', 12);
    $typePositions = [
      'In-Campus'  => [75, 62],
      'Off-Campus' => [124.3, 62],
    ];
    if ($request->type && isset($typePositions[$request->type])) {
      [$x, $y] = $typePositions[$request->type];
      $pdf->SetXY($x, $y);
      $pdf->Write(0, chr(52));
    }

    // 🖋 Nature Checkboxes
    $naturePositions = [
      'Training/Seminar'   => [75, 70],
      'Conference/Summit'  => [124.3, 70],
      'Culmination'        => [75, 74.3],
      'Socialization'      => [124.3, 74.3],
      'Meeting'            => [75, 78.3],
      'Concert'            => [124.3, 78.3],
      'Exhibit'            => [75, 82.6],
      'Program'            => [124.3, 82.6],
      'Educational Tour'   => [75, 86.9],
      'Clean and Green'    => [124.3, 86.9],
      'Competition'        => [75, 91.2],
      'Other'              => [124.3, 91.2],
    ];
    if ($request->nature && isset($naturePositions[$request->nature])) {
      [$x, $y] = $naturePositions[$request->nature];
      $pdf->SetXY($x, $y);
      $pdf->Write(0, chr(52));
    }

    if ($request->filled('nature_other')) {
      $pdf->SetFont('Helvetica', '', 12);
      $pdf->SetXY(138, 91.2);
      $pdf->Write(0, $request->nature_other);
    }

    //Dates & Times
    $startDate = strtotime($request->date_start);
    $endDate = $request->date_end ? strtotime($request->date_end) : null;
    if ($endDate && $endDate !== $startDate) {
      if (date('m', $startDate) === date('m', $endDate) && date('Y', $startDate) === date('Y', $endDate)) {
        $dateDisplay = date('m/d', $startDate) . '-' . date('d/Y', $endDate);
      } else {
        $dateDisplay = date('m/d/Y', $startDate) . ' - ' . date('m/d/Y', $endDate);
      }
    } else {
      $dateDisplay = date('m/d/Y', $startDate);
    }

    $startTime = date("g:i A", strtotime($request->time_start));
    $endTime = date("g:i A", strtotime($request->time_end));
    $timeDisplay = ($startTime === $endTime) ? $startTime : "$startTime - $endTime";

    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetXY(73.5, 95.5);
    $pdf->Write(0, $request->venue);
    $pdf->SetXY(73.5, 99.6);
    $pdf->Write(0, $dateDisplay);
    $pdf->SetXY(142, 99.6);
    $pdf->Write(0, $timeDisplay);

    // 🖋 Participants
    $pdf->SetFont('ZapfDingbats', '', 12);
    $participantPositions = [
      'Members'      => [75, 103.5],
      'Officers'     => [75, 107.7],
      'All Students' => [75, 111.8],
      'Other'        => [75, 116],
    ];
    if ($request->participants && isset($participantPositions[$request->participants])) {
      [$x, $y] = $participantPositions[$request->participants];
      $pdf->SetXY($x, $y);
      $pdf->Write(0, chr(52));
    }

    if ($request->filled('participants_other')) {
      $pdf->SetFont('Helvetica', '', 11);
      $pdf->SetXY(90, 116);
      $pdf->Write(0, $request->participants_other);
    }

    // 🖋 Number of Participants
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->SetXY(142, 110);
    $pdf->Write(0, $request->number);

    //prepared by (centered)
    $pdf->SetFont('Helvetica', '', 10);
    $text = strtoupper($request->name);
    $textWidth = $pdf->GetStringWidth($text);
    $centeredX = 47 - ($textWidth / 2);
    $pdf->SetXY($centeredX, 138);
    $pdf->Write(0, $text);
    // ✅ Signature (centered at same X as bottom name, 128)
    $signaturePath = null;
    if ($request->hasFile('signature_upload')) {
      $signaturePath = $request->file('signature_upload')->getPathName();
    } elseif ($request->filled('signature_data')) {
      $imgData = str_replace('data:image/png;base64,', '', $request->input('signature_data'));
      $img = str_replace(' ', '+', $imgData);
      $signaturePath = storage_path('app/temp_signature.png');
      file_put_contents($signaturePath, base64_decode($img));
    }

    if ($signaturePath && file_exists($signaturePath)) {
      $imageWidth = 40; // As per your code
      $centeredX = 47 - ($imageWidth / 2); // Center at 128
      $pdf->Image($signaturePath, $centeredX, 120, 40, 20);
    }


    // ✅ Signature (centered at same X as bottom name, 128)
    $signaturePath = null;
    if ($request->hasFile('signature_upload')) {
      $signaturePath = $request->file('signature_upload')->getPathName();
    } elseif ($request->filled('signature_data')) {
      $imgData = str_replace('data:image/png;base64,', '', $request->input('signature_data'));
      $img = str_replace(' ', '+', $imgData);
      $signaturePath = storage_path('app/temp_signature.png');
      file_put_contents($signaturePath, base64_decode($img));
    }

    if ($signaturePath && file_exists($signaturePath)) {
      $imageWidth = 40; // As per your code
      $centeredX = 153 - ($imageWidth / 2); // Center at 128
      $pdf->Image($signaturePath, $centeredX, 207, 40, 20);
    }

    // 🖋 Name at the bottom (centered)
    $text = strtoupper($request->name);
    $textWidth = $pdf->GetStringWidth($text);
    $centeredX = 153 - ($textWidth / 2);
    $pdf->SetXY($centeredX, 223);
    $pdf->Write(0, $text);

    // Save PDF data in DB
    $pdfData = $pdf->Output('S');
    $permit->update(['pdf_data' => $pdfData]);

    return response($pdfData)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="sdso_permit_filled.pdf"');
  }

  public function view($hashed_id)
  {
    $permit = Permit::where('hashed_id', $hashed_id)->firstOrFail();

    if ($permit->pdf_data) {
      return response($permit->pdf_data)
        ->header('Content-Type', 'application/pdf');
    }

    abort(404, 'PDF not available.');
  }

  public function status($id)
  {
    $permit = Permit::findOrFail($id);
    $approvals = EventApproval::where('event_id', $permit->id)->orderBy('id')->get();
    return view('student.permit.status', compact('permit', 'approvals'));
  }
  public function viewPdf($id)
  {
    $permit = Permit::findOrFail($id);

    // Ensure the permit actually has PDF data
    if (!$permit->pdf_data) {
      abort(404, 'PDF not found.');
    }

    return response($permit->pdf_data)
      ->header('Content-Type', 'application/pdf')
      ->header('Content-Disposition', 'inline; filename="permit.pdf"');
  }

  public function track()
  {
    $user = auth()->user();

    $permits = Permit::with([
      'organization',
      'approvals' // ✅ uses EventApprovalFlow model relation
    ])
      ->whereHas('organization', function ($q) use ($user) {
        $q->where('user_id', $user->user_id);
      })
      ->orderBy('created_at', 'desc')
      ->get();

    return view('student.permit.tracking', compact('permits'));
  }
}
