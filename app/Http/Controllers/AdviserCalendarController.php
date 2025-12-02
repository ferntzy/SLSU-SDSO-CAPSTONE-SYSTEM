<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AdviserCalendarController extends Controller
{
  public function index()
  {
    return view('adviser.calendar');
  }

  public function getEvents()
  {
    $adviserId = Auth::id();

    // Get all organizations this adviser advises
    $orgIds = \App\Models\Organization::where('adviser_id', $adviserId)
      ->pluck('organization_id')
      ->toArray();

    if (empty($orgIds)) {
      return response()->json([]);
    }

    // Fetch permits + organization name only
    $permits = DB::table('permits')
      ->join('organizations', 'permits.organization_id', '=', 'organizations.organization_id')
      ->whereIn('permits.organization_id', $orgIds)
      ->select(
        'permits.permit_id',
        'permits.title_activity',
        'permits.purpose',
        'permits.type',
        'permits.venue',           // ← This is the actual venue string
        'permits.date_start',
        'permits.date_end',
        'permits.time_start',
        'permits.time_end',
        'organizations.organization_name'
      )
      ->get();

    $events = [];

    foreach ($permits as $permit) {
      // ONLY show if FULLY approved by ALL roles including VP_SAS
      if (!$this->isFullyApprovedByAllIncludingVPSAS($permit->permit_id)) {
        continue;
      }

      // Use venue directly from permits.venue column
      $venueDisplay = $permit->venue && trim($permit->venue) !== ''
        ? trim($permit->venue)
        : 'Venue not specified';

      $isAllDay = empty($permit->time_start) || empty($permit->time_end);

      $start = $permit->date_start;
      $end   = null;

      if (!$isAllDay) {
        $start .= 'T' . substr($permit->time_start ?? '00:00', 0, 5);
        if ($permit->date_end && $permit->date_end !== $permit->date_start) {
          $end = $permit->date_end . 'T' . substr($permit->time_end ?? '23:59', 0, 5);
        }
      } else {
        // Multi-day all-day event
        if ($permit->date_end && $permit->date_end !== $permit->date_start) {
          $end = Carbon::parse($permit->date_end)->addDay()->format('Y-m-d');
        }
      }

      $events[] = [
        'title' => $permit->title_activity,
        'start' => $start,
        'end'   => $end,
        'allDay' => $isAllDay,
        'backgroundColor' => '#1e7e34',  // Final approval green
        'borderColor'     => '#1e7e34',
        'textColor'       => '#fff',
        'extendedProps' => [
          'organization_name' => $permit->organization_name,
          'venue'             => $venueDisplay,
          'purpose'           => $permit->purpose ?? 'No description provided',
          'type'              => $permit->type,
          'status'            => 'Fully Approved by VP-SAS',
        ]
      ];
    }

    return response()->json($events);
  }

  /**
   * Check if a permit is FULLY approved by ALL required roles INCLUDING VP_SAS
   */
  private function isFullyApprovedByAllIncludingVPSAS($permitId)
  {
    $requiredRoles = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];

    $approvals = DB::table('event_approval_flow')
      ->where('permit_id', $permitId)
      ->whereIn('approver_role', $requiredRoles)
      ->pluck('status', 'approver_role');

    // Must have exactly 5 approvals (one for each role)
    if ($approvals->count() !== 5) {
      return false;
    }

    // VP_SAS must have approved
    if (!isset($approvals['VP_SAS']) || $approvals['VP_SAS'] !== 'approved') {
      return false;
    }

    // All 5 must be 'approved'
    return $approvals->every(fn($status) => $status === 'approved');
  }

  private function getPermitStatus($permitId)
  {
    $flow = DB::table('event_approval_flow')
      ->where('permit_id', $permitId)
      ->where('approver_role', 'Faculty_Adviser')
      ->first();

    if (!$flow) return 'Pending';

    return match ($flow->status) {
      'approved' => 'Approved',
      'rejected' => 'Rejected',
      default => 'Pending'
    };
  }
}
