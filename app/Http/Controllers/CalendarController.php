<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CalendarController extends Controller
{
  /**
   * Fetch all events for the calendar
   * This fetches permits and checks their approval status
   * Only returns APPROVED events
   */

public function getEvents()
{
    $permits = DB::table('permits')
        ->join('organizations', 'permits.organization_id', '=', 'organizations.organization_id')
        ->leftJoin('venues', fn($j) => $j->on(DB::raw('CAST(permits.venue AS UNSIGNED)'), '=', 'venues.venue_id')
            ->where('permits.type', '=', 'In-Campus'))
        ->select('permits.*', 'organizations.organization_name', 'venues.venue_name')
        ->get();

    $events = [];

    foreach ($permits as $permit) {
        $approvalStatus = $this->getPermitApprovalStatus($permit->permit_id);
        if ($approvalStatus !== 'Approved') continue;

        $isBargoEvent = false; // Default

        // Detect if this event was created by BARGO (you can improve this logic)
        if (str_contains(strtoupper($permit->title_activity), 'BARGO') ||
            str_contains(strtoupper($permit->purpose ?? ''), 'BARGO')) {
            $isBargoEvent = true;
        }

        $events[] = [
            'title' => $permit->title_activity,
            'start' => $permit->date_start . ($permit->time_start ? 'T' . substr($permit->time_start, 0, 5) : ''),
            'end'   => $permit->date_end && $permit->time_end
                ? $permit->date_end . 'T' . substr($permit->time_end, 0, 5)
                : null,
            'allDay' => empty($permit->time_start),
            'color' => $isBargoEvent ? '#ff851b' : '#28a745', // ORANGE for BARGO
            'extendedProps' => [
                'venue' => $permit->venue_name ?? $permit->venue,
                'organization_name' => $permit->organization_name,
                'purpose' => $permit->purpose,
                'type' => $permit->type,
                'is_bargo_event' => $isBargoEvent
            ]
        ];
    }

    return response()->json($events);
}


  /**
   * Check the approval status of a permit by examining event_approval_flow
   */
  private function getPermitApprovalStatus($permitId)
  {
    // Get all approval records for this permit
    $approvals = DB::table('event_approval_flow')
      ->where('permit_id', $permitId)
      ->get();

    // If no approvals exist yet, it's pending
    if ($approvals->isEmpty()) {
      return 'Pending';
    }

    // Check if any approval is rejected
    $hasRejection = $approvals->where('status', 'rejected')->isNotEmpty();
    if ($hasRejection) {
      return 'Rejected';
    }

    // Check if all approvals are approved
    $allApproved = $approvals->every(function ($approval) {
      return $approval->status === 'approved';
    });

    if ($allApproved) {
      return 'Approved';
    }

    // Otherwise, still pending
    return 'Pending';
  }

  /**
   * Get color based on approval status
   */
  private function getStatusColor($status)
{
    return match($status) {
        'approved' => '#28a745',
        'rejected' => '#dc3545',
        default    => '#ffc107',
    };
}

  /**
   * Store a new permit (existing method - may need adjustment based on your current implementation)
   */
  public function store(Request $request)
  {
    try {
      // Validate the request
      $validated = $request->validate([
        'title_activity' => 'required|string|max:255',
        'purpose' => 'required|string',
        'type' => 'required|in:In-Campus,Off-Campus',
        'nature' => 'required|string',
        'venue_id' => 'required_if:type,In-Campus',
        'venue_other' => 'required_if:type,Off-Campus|string|max:255',
        'date_start' => 'required|date',
        'date_end' => 'nullable|date|after_or_equal:date_start',
        'time_start' => 'nullable|date_format:h:i A',
        'time_end' => 'nullable|date_format:h:i A|after:time_start',
        'participants' => 'required|string',
        'number' => 'required|integer|min:1',
      ]);

      // Get the organization_id from the authenticated user
      $user = auth()->user();
      $organization = DB::table('organizations')
        ->where('user_id', $user->user_id)
        ->first();

      if (!$organization) {
        return response()->json(
          [
            'success' => false,
            'message' => 'No organization found for this user.',
          ],
          403
        );
      }

      // Determine venue
      $venueToCheck = $request->type === 'In-Campus' ? $request->venue_id : $request->venue_other;

      $proposedStart = Carbon::parse($request->date_start . ' ' . ($request->time_start ?? '00:00'));
      $proposedEnd   = Carbon::parse(
        ($request->date_end ?? $request->date_start) . ' ' . ($request->time_end ?? '23:59')
      );


      // Check for conflicts
      $conflicting = DB::table('permits')
    ->join('event_approval_flow', 'permits.permit_id', '=', 'event_approval_flow.permit_id')
    ->where(function ($query) use ($venueToCheck, $request) {
        if ($request->type === 'In-Campus') {
            $query->where('permits.venue', $venueToCheck)
                  ->where('permits.type', 'In-Campus');
        } else {
            $query->where('permits.venue', $venueToCheck)
                  ->where('permits.type', 'Off-Campus');
        }
    })
    // Only consider permits that are FULLY APPROVED
    ->whereIn('permits.permit_id', function ($q) {
        $q->select('permit_id')
          ->from('event_approval_flow')
          ->groupBy('permit_id')
          ->havingRaw('COUNT(CASE WHEN status = "rejected" THEN 1 END) = 0') // No rejections
          ->havingRaw('COUNT(CASE WHEN status = "approved" THEN 1 END) = COUNT(*)'); // All approved
    })
    ->select('permits.*')
    ->get()
    ->filter(function ($permit) use ($proposedStart, $proposedEnd) {
        $existingStart = Carbon::parse($permit->date_start . ' ' . ($permit->time_start ?? '00:00'));
        $existingEnd   = Carbon::parse(
            ($permit->date_end ?? $permit->date_start) . ' ' . ($permit->time_end ?? '23:59')
        );

        // True if the two events overlap at all
        return $proposedStart < $existingEnd && $proposedEnd > $existingStart;
    });

      if ($conflicting->isNotEmpty()) {
    $conflictList = $conflicting->map(function ($p) {
        return "- {$p->title_activity} ({$p->date_start}"
             . ($p->date_end && $p->date_end != $p->date_start ? " to {$p->date_end}" : "")
             . ", {$p->time_start} - {$p->time_end})";
    })->implode("\n");

    return response()->json([
        'success' => false,
        'message' => "Schedule conflict detected with the following approved event(s):\n\n{$conflictList}\n\nPlease choose a different date, time, or venue.",
    ], 422);
}

      // Handle signature
      $signatureData = null;
      if ($request->has('signature_data')) {
        $signatureData = $request->signature_data;
      }

      // Insert permit
      $permitId = DB::table('permits')->insertGetId([
        'organization_id' => $organization->organization_id,
        'title_activity' => $request->title_activity,
        'purpose' => $request->purpose,
        'type' => $request->type,
        'nature' => $request->nature,
        'venue' => $venue,
        'date_start' => $request->date_start,
        'date_end' => $request->date_end,
        'time_start' => $request->time_start ? date('H:i:s', strtotime($request->time_start)) : null,
        'time_end' => $request->time_end ? date('H:i:s', strtotime($request->time_end)) : null,
        'participants' => $request->participants,
        'number' => $request->number,
        'signature_data' => $signatureData,
        'created_at' => now(),
        'updated_at' => now(),
      ]);

      // Initialize approval flow with pending status for all required roles
      $roles = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];

      foreach ($roles as $role) {
        DB::table('event_approval_flow')->insert([
          'permit_id' => $permitId,
          'approver_role' => $role,
          'status' => 'pending',
          'created_at' => now(),
          'updated_at' => now(),
        ]);
      }

      return response()->json([
        'success' => true,
        'message' => 'Permit submitted successfully! Awaiting approval.',
        'permit_id' => $permitId,
      ]);
    } catch (\Illuminate\Validation\ValidationException $e) {
      return response()->json(
        [
          'success' => false,
          'message' => 'Validation failed.',
          'errors' => $e->errors(),
        ],
        422
      );
    } catch (\Exception $e) {
      \Log::error('Permit Store Error: ' . $e->getMessage());
      return response()->json(
        [
          'success' => false,
          'message' => 'An error occurred while submitting the permit: ' . $e->getMessage(),
        ],
        500
      );
    }
  }
  private function isPermitFullyApproved($permitId)
  {
    return DB::table('event_approval_flow')
      ->where('permit_id', $permitId)
      ->where('status', '!=', 'approved')
      ->orWhereNull('status')
      ->count() === 0
      && DB::table('event_approval_flow')->where('permit_id', $permitId)->where('status', 'rejected')->count() === 0;
  }
}
