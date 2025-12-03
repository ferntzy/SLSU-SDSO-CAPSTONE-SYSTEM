<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;          // ← add this too
use Illuminate\Http\Request;

class PermitTrackingController extends Controller
{
  /**
   * Get the organization ID that the authenticated user belongs to
   * Uses: users → user_profiles → members → organizations
   */
  private function getUserOrganizationId()
  {
    $user = auth()->user();

    // THIS IS THE MAGIC LINE — gets profile_id directly from DB, bypassing Laravel cache
    $profileId = \DB::table('users')->where('user_id', $user->user_id)->value('profile_id');

    if (!$profileId) {
      abort(403, 'Your account has no linked profile.');
    }

    $member = \DB::table('members')
      ->where('profile_id', $profileId)
      ->first();

    if (!$member) {
      // Optional: auto-add for testing (remove later if you want)
      \DB::table('members')->insert([
        'profile_id' => $profileId,
        'organization_id' => 9,
        'membership_status' => 'active',
        'joined_at' => now(),
        'updated_at' => now(),
      ]);
      return 9;
    }

    return $member->organization_id;
  }

  /**
   * Base query for permits of the user's organization
   */
  private function getOrganizationPermitsQuery()
  {
    $orgId = $this->getUserOrganizationId();

    return Permit::query()
      ->with(['approvalFlow' => fn($q) => $q->orderBy('created_at')])
      ->with(['approvalFlow.approver.profile'])
      ->where('organization_id', $orgId)
      ->latest('created_at');
  }

  /**
   * Share permit counts in sidebar (cached for 5 minutes)
   */
  private function sharePermitCounts()
  {
    if (View::shared('pendingPermitsCount') !== null) return;

    $orgId = $this->getUserOrganizationId();

    $counts = Cache::remember("org_{$orgId}_permit_counts", 300, function () use ($orgId) {
      $permits = Permit::with('approvalFlow')->where('organization_id', $orgId)->get();

      $pending = $approved = $rejected = 0;

      foreach ($permits as $permit) {
        $flow = $permit->approvalFlow;

        $hasRejected = $flow->contains('status', 'rejected');
        $hasPending = $flow->isEmpty() || $flow->contains('status', 'pending');
        $vpApproved = $flow->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty();

        if ($hasRejected) {
          $rejected++;
        } elseif ($vpApproved && !$hasPending) {
          $approved++;
        } else {
          $pending++;
        }
      }

      return compact('pending', 'approved', 'rejected');
    });

    View::share([
      'pendingPermitsCount'   => $counts['pending'],
      'approvedPermitsCount'  => $counts['approved'],
      'rejectedPermitsCount' => $counts['rejected'],
    ]);
  }

  // ==================================================================
  // MAIN TRACKING DASHBOARD
  // ==================================================================
  public function track()
  {

    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()->get();
    $now = now();

    $pendingPermits = $permits->filter(
      fn($p) => ($p->approvalFlow->isEmpty() || $p->approvalFlow->contains('status', 'pending')) &&
        !$p->approvalFlow->contains('status', 'rejected')
    );

    $rejectedPermits = $permits->filter(fn($p) => $p->approvalFlow->contains('status', 'rejected'));

    $approvedPermits = $permits->filter(
      fn($p) =>
      $p->approvalFlow->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty() &&
        !$p->approvalFlow->whereIn('status', ['pending', 'rejected'])->count()
    );

    $ongoingEvents = $approvedPermits->filter(function ($p) use ($now) {
      $start = $p->date_start ? \Carbon\Carbon::parse($p->date_start) : null;
      $end   = $p->date_end ? \Carbon\Carbon::parse($p->date_end) : null;

      // Must have started
      if (!$start || $start->gt($now)) {
        return false;
      }

      // If has end date → must not have ended yet
      // If no end date → consider ongoing as long as it has started
      if ($end) {
        return $end->endOfDay() >= $now; // includes today
      }

      return true; // no end date → still ongoing
    });

    // Successful: Approved + has ended (has end date AND end date passed)
    $successfulEvents = $approvedPermits->filter(function ($p) use ($now) {
      $end = $p->date_end ? \Carbon\Carbon::parse($p->date_end) : null;
      return $end && $end->endOfDay() < $now;
    });

    return view('student.permit.tracking', compact(
      'pendingPermits',
      'approvedPermits',
      'rejectedPermits',
      'ongoingEvents',
      'successfulEvents'
    ));
  }

  // ==================================================================
  // INDIVIDUAL PAGES
  // ==================================================================
  public function pendingPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->where(function ($q) {
        $q->whereHas('approvalFlow', fn($sq) => $sq->where('status', 'pending'))
          ->orWhereDoesntHave('approvalFlow');
      })
      ->whereDoesntHave('approvalFlow', fn($q) => $q->where('status', 'rejected'))
      ->paginate(10);

    return view('student.page.pending', compact('permits'));
  }

  public function approvedPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->whereHas(
        'approvalFlow',
        fn($q) =>
        $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
      )
      ->whereDoesntHave(
        'approvalFlow',
        fn($q) =>
        $q->whereIn('status', ['pending', 'rejected'])
      )
      ->paginate(10);

    return view('student.page.approved', compact('permits'));
  }

  public function rejectedPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->whereHas('approvalFlow', fn($q) => $q->where('status', 'rejected'))
      ->paginate(10);

    return view('student.page.rejected', compact('permits'));
  }

  // Legacy redirects
  public function index()
  {
    return $this->track();
  }
  public function pending()
  {
    return $this->pendingPage();
  }
  public function approved()
  {
    return $this->approvedPage();
  }
  public function rejected()
  {
    return $this->rejectedPage();
  }
  public function successfulPage()
  {
    $this->sharePermitCounts();

    $now = now();

    $successfulEvents = $this->getOrganizationPermitsQuery()
      ->whereNotNull('date_end')
      ->where('date_end', '<', $now->endOfDay())
      ->whereHas(
        'approvalFlow',
        fn($q) =>
        $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
      )
      ->whereDoesntHave(
        'approvalFlow',
        fn($q) =>
        $q->whereIn('status', ['pending', 'rejected'])
      )
      ->with(['reports' => fn($q) => $q->latest()])
      ->paginate(10);

    return view('student.page.successful', compact('successfulEvents'));
  }
  public function storeReport(Request $request)
  {
    $request->validate([
      'event_id' => 'required|exists:permits,permit_id',
      'document_type' => 'required|string|max:100',
      'title' => 'nullable|string|max:255',
      'description' => 'nullable|string',
      'documents.*' => 'required|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:10240'
    ]);

    $permit = Permit::findOrFail($request->event_id);

    if ($permit->organization_id !== $this->getUserOrganizationId()) {
      abort(403);
    }

    foreach ($request->file('documents') as $file) {
      $hashedName = $file->hashName(); // e.g., abc123xyz.jpg
      $path = $file->storeAs('reports/' . $permit->permit_id, $hashedName, 'public');

      Report::create([
        'event_id' => $permit->permit_id,
        'document_type' => $request->document_type,
        'title' => $request->title,
        'description' => $request->description,
        'document_url' => $path,
        'original_filename' => $file->getClientOriginalName(),
        'mime_type' => $file->getMimeType(),
        'file_size' => $file->getSize(),
      ]);


      return redirect()
        ->route('student.reports.show', $permit->permit_id)
        ->with('success', 'Files uploaded successfully! View them below.');
    }

    return back()->with('success', 'Files uploaded successfully!');
  }
  public function ongoingPage()
{
    $this->sharePermitCounts();

    $now = now();                    // e.g., 2025-12-03 18:45:22
    $today = $now->toDateString();   // 2025-12-03
    $timeNow = $now->format('H:i:s'); // 18:45:22

    $ongoingEvents = $this->getOrganizationPermitsQuery()
        ->whereNotNull('date_start')
        ->where(function ($query) use ($today, $now) {
            // The event is happening today or started in the past and ends today or later
            $query->where('date_start', '<=', $today)
                  ->where(function ($q) use ($today) {
                      $q->whereNull('date_end')
                        ->orWhere('date_end', '>=', $today);
                  });
        })
        ->where(function ($query) use ($timeNow) {
            // Critical: Current time must be between time_start and time_end
            $query->whereRaw(
                "CAST(? AS TIME) BETWEEN
                 COALESCE(TIME(time_start), '00:00:00') AND
                 COALESCE(TIME(time_end), '23:59:59')",
                [$timeNow]
            );
        })
        ->whereHas('approvalFlow', fn($q) =>
            $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
        )
        ->whereDoesntHave('approvalFlow', fn($q) =>
            $q->whereIn('status', ['pending', 'rejected'])
        )
        ->latest('date_start')
        ->latest('time_start')
        ->paginate(15);

    return view('student.page.ongoing', compact('ongoingEvents'));
}
  public function showReports($hashed_id)
  {
    try {
      $permitId = Crypt::decryptString($hashed_id);
    } catch (\Exception $e) {
      abort(404);
    }
    $permit = Permit::with('reports')->findOrFail($permitId);

    if ($permit->organization_id !== $this->getUserOrganizationId()) {
      abort(403);
    }

    return view('student.reports.show', compact('permit'));
  }
  public function markAsCompleted(Request $request, Permit $permit)
  {
    if ($permit->organization_id !== $this->getUserOrganizationId()) {
      abort(403);
    }

    $permit->update([
      'is_completed' => true,
      'completed_at' => now(),
    ]);

    return back()->with('success', 'Event marked as completed! It has been moved to Successful Events.');
  }
  // In PermitTrackingController.php
  public function submitToSdso(Request $request, $hashed_id)
  {
    try {
      $permitId = Crypt::decryptString($hashed_id);
    } catch (\Exception $e) {
      return back()->with('swal', [
        'title' => 'Error!',
        'text'  => 'Invalid link.',
        'icon'  => 'error'
      ]);
    }

    $permit = \App\Models\Permit::find($permitId);

    if (!$permit) {
      return back()->with('swal', ['title' => 'Not Found', 'text' => 'Event not found.', 'icon' => 'error']);
    }

    // THIS IS THE REAL FIX — check via the organization relationship
    $user = auth()->user();

    // Option A (most common): user has a direct organization relationship
    if ($user->organization && $user->organization->id !== $permit->organization_id) {
      return back()->with('swal', [
        'title' => 'Access Denied',
        'text'  => 'You do not own this event.',
        'icon'  => 'warning'
      ]);
    }

    // Option B (fallback): if user has organization_id directly on users table
    // Remove this line completely if your users table doesn't have organization_id
    // if ($user->organization_id && $user->organization_id !== $permit->organization_id) { ... }

    // No files check
    if ($permit->reports()->count() === 0) {
      return back()->with('swal', [
        'title' => 'No Files',
        'text'  => 'Please upload at least one file first.',
        'icon'  => 'info'
      ]);
    }

    // SUCCESS — update database
    $permit->update([
      'is_completed' => 1,
      'completed_at' => now(),
    ]);

    return back()->with('swal', [
      'title' => 'Submitted!',
      'text'  => 'Documentation successfully sent to SDSO!',
      'icon'  => 'success',
      'timer' => 3000
    ]);
  }
public function submissionsHistory()
{
    $this->sharePermitCounts(); // assuming this shares $pendingPermits, etc.

    return view('student.page.submissions-history');
}
}
