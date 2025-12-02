<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

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

    $ongoingEvents = $approvedPermits->filter(
      fn($p) =>
      $p->date_start <= $now && (!$p->date_end || $p->date_end >= $now)
    );

    $successfulEvents = $approvedPermits->filter(
      fn($p) =>
      $p->date_end && $p->date_end < $now
    );

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
}
