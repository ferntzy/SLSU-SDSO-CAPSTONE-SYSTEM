<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use App\Models\Organization;

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

    public function approvals()
    {
        $organizationIds = $this->getAdvisedOrganizationIds();

        $permitIds = Permit::whereIn('organization_id', $organizationIds)
            ->pluck('permit_id');

        $pendingPermits = EventApprovalFlow::with(['permit.organization'])
            ->where('approver_role', 'Faculty_Adviser')
            ->where('status', 'pending')
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
            ->header('Content-Disposition', 'inline; filename="permit_'.$hashed_id.'.pdf"');
    }

    // approve() and reject() methods remain exactly as you had them
    // (they are already perfect)
}
