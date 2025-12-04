{{-- resources/views/bargo/dashboard.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'BARGO Dashboard')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @php
        $now = now()->setTimezone('Asia/Manila');
        $hour = $now->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

        $thisMonth = now()->startOfMonth();
        $approvedThisMonth = \App\Models\EventApprovalFlow::where('approver_role', 'BARGO')
            ->where('status', 'approved')
            ->where('updated_at', '>=', $thisMonth)
            ->count();

        $rejectedThisMonth = \App\Models\EventApprovalFlow::where('approver_role', 'BARGO')
            ->where('status', 'rejected')
            ->where('updated_at', '>=', $thisMonth)
            ->count();

        $approvalRate = $approvedThisMonth + $rejectedThisMonth > 0
            ? round(($approvedThisMonth / ($approvedThisMonth + $rejectedThisMonth)) * 100, 1)
            : 0;
    @endphp

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white" style="background: linear-gradient(135deg, #696cff 0%, #8b8dff 100%);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-white mb-2 fw-semibold">{{ $greeting }},  {{ Auth::user()->profile?->first_name }}</h4>
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <span class="badge bg-white text-primary">
                                    <i class="mdi mdi-calendar-month me-1"></i>{{ $now->format('l, F j, Y') }}
                                </span>
                                <span class="badge bg-white text-primary">
                                    <i class="mdi mdi-clock-outline me-1"></i>{{ $now->format('h:i A') }}
                                </span>
                            </div>

                        </div>
                        <div class="col-md-4 text-md-end mt-3 mt-md-0">
                            <h5 class="text-white fw-semibold mb-1">BARGO Review Dashboard</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <!-- Pending Reviews -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="badge bg-label-warning rounded-pill mb-2">Pending</span>
                            <h3 class="mb-1 fw-bold">{{ $pendingReviews }}</h3>
                            <small class="text-muted">Awaiting Your Review</small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="mdi mdi-clock-alert-outline mdi-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approved -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="badge bg-label-success rounded-pill mb-2">Approved</span>
                            <h3 class="mb-1 fw-bold">{{ $approved }}</h3>
                            <small class="text-muted">Forwarded to SDSO Head</small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="mdi mdi-check-bold mdi-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="badge bg-label-danger rounded-pill mb-2">Rejected</span>
                            <h3 class="mb-1 fw-bold">{{ $rejected }}</h3>
                            <small class="text-muted">Returned to Organization</small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="mdi mdi-close-thick mdi-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Processed -->
        <div class="col-xl-3 col-sm-6">
            <div class="card h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="flex-grow-1">
                            <span class="badge bg-label-primary rounded-pill mb-2">Total</span>
                            <h3 class="mb-1 fw-bold">{{ $approved + $rejected }}</h3>
                            <small class="text-muted">Your Total Actions</small>
                        </div>
                        <div class="avatar flex-shrink-0">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="mdi mdi-counter mdi-24px"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Performance Analytics -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-chart-line text-primary me-2"></i>
                        BARGO Performance This Month
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <!-- Approved This Month -->
                        <div class="col-md-4">
                            <div class="border rounded p-4 text-center h-100">
                                <div class="avatar mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-label-success">
                                        <i class="mdi mdi-trending-up mdi-24px"></i>
                                    </span>
                                </div>
                                <h6 class="text-muted mb-2">Approved This Month</h6>
                                <h2 class="text-success fw-bold mb-2">{{ $approvedThisMonth }}</h2>
                                <small class="text-muted">Permits Forwarded</small>
                            </div>
                        </div>

                        <!-- Rejection Rate -->
                        <div class="col-md-4">
                            <div class="border rounded p-4 text-center h-100">
                                <div class="avatar mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-label-danger">
                                        <i class="mdi mdi-trending-down mdi-24px"></i>
                                    </span>
                                </div>
                                <h6 class="text-muted mb-2">Rejection Rate</h6>
                                <h2 class="text-danger fw-bold mb-2">{{ $rejectedThisMonth }}</h2>
                                <small class="text-muted">({{ 100 - $approvalRate }}%) Returned for Revision</small>
                            </div>
                        </div>

                        <!-- Approval Rate -->
                        <div class="col-md-4">
                            <div class="border rounded p-4 text-center h-100">
                                <div class="avatar mx-auto mb-3">
                                    <span class="avatar-initial rounded-circle bg-label-primary">
                                        <i class="mdi mdi-percent-outline mdi-24px"></i>
                                    </span>
                                </div>
                                <h6 class="text-muted mb-2">Approval Rate</h6>
                                <h2 class="fw-bold mb-3" style="color: #696cff;">{{ $approvalRate }}%</h2>
                                <div class="progress mb-2" style="height: 8px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $approvalRate }}%"
                                        aria-valuenow="{{ $approvalRate }}"
                                        aria-valuemin="0"
                                        aria-valuemax="100">
                                    </div>
                                </div>
                                <small class="text-muted">Healthy Range: 85–95%</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <h5 class="mb-3 text-muted">
                <i class="mdi mdi-lightning-bolt-outline"></i>
                Quick Actions
            </h5>
        </div>

        <!-- Review Pending -->
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('bargo.pending') }}" class="text-decoration-none">
                <div class="card card-action h-100 hover-shadow">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="mdi mdi-file-clock-outline mdi-36px"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">Review Pending</h5>
                        <div class="badge bg-warning rounded-pill mb-2">{{ $pendingReviews }} Permits</div>
                        <p class="text-muted small mb-0">Review awaiting permits</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- View Approved -->
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('bargo.approved') }}" class="text-decoration-none">
                <div class="card card-action h-100 hover-shadow">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="mdi mdi-check-all mdi-36px"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">View Approved</h5>
                        <div class="badge bg-success rounded-pill mb-2">{{ $approved }} Total</div>
                        <p class="text-muted small mb-0">See approved permits</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- Full History -->
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('bargo.history') }}" class="text-decoration-none">
                <div class="card card-action h-100 hover-shadow">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="mdi mdi-history mdi-36px"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">Full History</h5>
                        <div class="badge bg-primary rounded-pill mb-2">{{ $approved + $rejected }} Total</div>
                        <p class="text-muted small mb-0">View all records</p>
                    </div>
                </div>
            </a>
        </div>

        <!-- View Rejected -->
        <div class="col-lg-3 col-md-6">
            <a href="{{ route('bargo.rejected') }}" class="text-decoration-none">
                <div class="card card-action h-100 hover-shadow">
                    <div class="card-body text-center py-4">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="mdi mdi-alert-remove mdi-36px"></i>
                            </span>
                        </div>
                        <h5 class="mb-2">View Rejected</h5>
                        <div class="badge bg-danger rounded-pill mb-2">{{ $rejected }} Total</div>
                        <p class="text-muted small mb-0">See rejected permits</p>
                    </div>
                </div>
            </a>
        </div>
    </div>


</div>

<style>
.hover-shadow {
    transition: all 0.3s ease;
}

.hover-shadow:hover {
    transform: translateY(-5px);
    box-shadow: 0 8px 24px rgba(67, 89, 113, 0.2) !important;
}

.card-action:hover {
    cursor: pointer;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
}

.bg-opacity-25 {
    --bs-bg-opacity: 0.25;
}

.card {
    box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
    border: none;
    border-radius: 0.5rem;
}
</style>
@endsection
