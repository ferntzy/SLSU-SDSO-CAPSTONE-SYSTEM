{{-- resources/views/adviser/dashboard.blade.php --}}
@extends('layouts.adviserLayout')

@section('title', 'Adviser Dashboard')

@section('content')
<div class="row mb-5">
    <div class="col-12">
        <h2 class="fw-bold">Welcome back, {{ Auth::user()->profile->first_name ?? 'Adviser' }}!</h2>
        <p class="text-muted">You are advising {{ Auth::user()->advisedOrganizations()->count() }} organization(s)</p>
    </div>
</div>

<div class="row g-4">
    @php
        $orgIds = Auth::user()->advisedOrganizations()->pluck('organization_id');

        $totalPermits   = \DB::table('permits')->whereIn('organization_id', $orgIds)->count();

        $unreadRequests = \DB::table('notifications')
            ->where('user_id', Auth::id())
            ->where('status', 'unread')
            ->where('notification_type', 'event_approval')
            ->count();

        $fullyApproved  = \DB::table('permits')
            ->whereIn('organization_id', $orgIds)
            ->whereExists(function($q) {
                $q->select(\DB::raw(1))
                  ->from('event_approval_flow')
                  ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
                  ->whereIn('approver_role', ['Faculty_Adviser','BARGO','SDSO_Head','SAS_Director','VP_SAS'])
                  ->groupBy('permit_id')
                  ->havingRaw('COUNT(*) = 5 AND SUM(status = "approved") = 5');
            })
            ->count();

        $rejectedPermits = \DB::table('permits')
            ->whereIn('organization_id', $orgIds)
            ->whereExists(function($q) {
                $q->from('event_approval_flow')
                  ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
                  ->where('status', 'rejected');
            })
            ->count();
    @endphp

    <!-- Total Permits -->
    <div class="col-md-3 col-6">
        <div class="card h-100 text-center shadow-sm border-0">
            <div class="card-body py-4">
                <h3 class="mb-1">{{ $totalPermits }}</h3>
                <p class="text-muted mb-0">Total Permits</p>
            </div>
        </div>
    </div>

    <!-- New Requests (Same as Bell) -->
    <div class="col-md-3 col-6">
        <div class="card h-100 text-center shadow-sm border-0 bg-warning bg-opacity-10 position-relative">
            <div class="card-body py-4">
                <h3 class="mb-1 text-warning fw-bold">{{ $unreadRequests }}</h3>
                <p class="mb-0 fw-bold">New Requests</p>
                @if($unreadRequests > 0)
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                        {{ $unreadRequests }}
                    </span>
                @endif
            </div>
        </div>
    </div>

    <!-- Fully Approved -->
    <div class="col-md-3 col-6">
        <div class="card h-100 text-center shadow-sm border-0 bg-success bg-opacity-10">
            <div class="card-body py-4">
                <h3 class="mb-1 text-success fw-bold">{{ $fullyApproved }}</h3>
                <p class="mb-0 fw-bold">Fully Approved</p>
            </div>
        </div>
    </div>

    <!-- Rejected -->
    <div class="col-md-3 col-6">
        <div class="card h-100 text-center shadow-sm border-0 bg-danger bg-opacity-10">
            <div class="card-body py-4">
                <h3 class="mb-1 text-danger fw-bold">{{ $rejectedPermits }}</h3>
                <p class="mb-0 fw-bold">Rejected</p>
            </div>
        </div>
    </div>
</div>
@endsection
