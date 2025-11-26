<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use App\Models\Organization;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;

class PermitTrackingController extends Controller
{
    /**
     * Get the organization ID that belongs to the authenticated user
     */
    private function getUserOrganizationId()
    {
        $user = auth()->user();

        // Critical fix: your `users` table uses `user_id`, not `id`
        // So auth()->user()->user_id is correct
        $organization = Organization::where('user_id', $user->user_id)->first();

        if (!$organization) {
            Log::warning('No organization found for user', [
                'user_id' => $user->user_id,
                'username' => $user->username,
                'role' => $user->account_role
            ]);

            abort(403, "You are not associated with any student organization.");
        }

        return $organization->organization_id;
    }

    /**
     * Base query for permits belonging to the user's organization
     */
    private function getOrganizationPermitsQuery()
    {
        $orgId = $this->getUserOrganizationId();

        return Permit::with([
                'approvals' => fn($q) => $q->orderBy('created_at'),
                'approvals.approver.profile'
            ])
            ->where('organization_id', $orgId)
            ->latest('created_at');
    }

    /**
     * Share permit counts in views (cached per organization)
     */
    private function sharePermitCounts()
    {
        if (View::shared('pendingPermitsCount') !== null) return;

        $orgId = $this->getUserOrganizationId();

        $counts = Cache::remember("org_{$orgId}_permit_counts", 300, function () use ($orgId) {
            $permits = Permit::with('approvals')->where('organization_id', $orgId)->get();

            $pending = 0;
            $approved = 0;
            $rejected = 0;

            foreach ($permits as $permit) {
                $approvals = $permit->approvals;

                $hasRejected = $approvals->contains('status', 'rejected');
                $hasPending = $approvals->isEmpty() || $approvals->contains('status', 'pending');
                $vpSasApproved = $approvals->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty();

                if ($hasRejected) {
                    $rejected++;
                } elseif ($vpSasApproved && !$hasPending) {
                    $approved++;
                } elseif ($hasPending || $approvals->isEmpty()) {
                    $pending++;
                }
            }

            return compact('pending', 'approved', 'rejected');
        });

        View::share([
            'pendingPermitsCount'   => $counts['pending'],
            'approvedPermitsCount'  => $counts['approved'],
            'rejectedPermitsCount'  => $counts['rejected'],
        ]);
    }

    /**
     * Main tracking dashboard
     */
    public function track()
    {
        $this->sharePermitCounts();

        $permits = $this->getOrganizationPermitsQuery()->get();

        $now = now();

        $pendingPermits = $permits->filter(function ($permit) {
            $approvals = $permit->approvals;
            return ($approvals->isEmpty() || $approvals->contains('status', 'pending'))
                && !$approvals->contains('status', 'rejected');
        });

        $rejectedPermits = $permits->filter(fn($p) => $p->approvals->contains('status', 'rejected'));

        $approvedPermits = $permits->filter(function ($permit) {
            $approvals = $permit->approvals;
            return $approvals->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty()
                && !$approvals->whereIn('status', ['pending', 'rejected'])->count();
        });

        $ongoingEvents = $approvedPermits->filter(function ($permit) use ($now) {
            return $permit->date_start <= $now && (!$permit->date_end || $permit->date_end >= $now);
        });

        $successfulEvents = $approvedPermits->filter(fn($p) => $p->date_end && $p->date_end < now());

        $canceledEvents = $rejectedPermits;

        return view('student.permit.tracking', compact(
            'pendingPermits',
            'approvedPermits',
            'rejectedPermits',
            'ongoingEvents',
            'successfulEvents',
            'canceledEvents'
        ));
    }

    /**
     * Pending permits page
     */
    public function pendingPage()
    {
        $this->sharePermitCounts();

        $permits = $this->getOrganizationPermitsQuery()
            ->where(function ($query) {
                $query->whereHas('approvals', fn($q) => $q->where('status', 'pending'))
                      ->orWhereDoesntHave('approvals');
            })
            ->whereDoesntHave('approvals', fn($q) => $q->where('status', 'rejected'))
            ->paginate(10);

        return view('student.page.pending', compact('permits'));
    }

    /**
     * Approved permits page
     */
    public function approvedPage()
    {
        $this->sharePermitCounts();

        $permits = $this->getOrganizationPermitsQuery()
            ->whereHas('approvals', fn($q) =>
                $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
            )
            ->whereDoesntHave('approvals', fn($q) =>
                $q->whereIn('status', ['pending', 'rejected'])
            )
            ->paginate(10);

        return view('student.page.approved', compact('permits'));
    }

    /**
     * Rejected permits page
     */
    public function rejectedPage()
{
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
        ->whereHas('approvals', fn($q) => $q->where('status', 'rejected'))
        ->paginate(10);

    return view('student.permit.rejected', compact('permits'));
        //                     ↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑↑
        //             This must match your actual Blade file path
}

    // Legacy route redirects
    public function index()     { return $this->track(); }
    public function pending()   { return $this->pendingPage(); }
    public function approved()  { return $this->approvedPage(); }
    public function rejected()  { return $this->rejectedPage(); }
}
