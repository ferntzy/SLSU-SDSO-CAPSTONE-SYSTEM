{{-- resources/views/student/dashboard.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Permit Analytics Dashboard')

@section('page-style')
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border: none;
        border-radius: 0.75rem;
    }
    .stat-card:hover {
        transform: translateY(-6px);
        box-shadow: 0 0.5rem 1.5rem rgba(67, 89, 113, 0.15) !important;
    }
    .stat-icon {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.5rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    @media (max-width: 768px) {
        .chart-container { height: 250px; }
    }
</style>
@endsection

@section('content')
@php
    $approvedPermits    = $permits->where('status', 'approved');
    $pendingPermits     = $permits->where('status', 'pending');
    $rejectedPermits    = $permits->where('status', 'rejected');

    $ongoingEvents      = $permits->where('event_status', 'ongoing');
    $successfulEvents   = $permits->where('event_status', 'successful');
    $canceledEvents     = $permits->where('event_status', 'canceled');

    $totalPermits       = $permits->count();
    $approvalRate       = $totalPermits > 0 ? round(($approvedPermits->count() / $totalPermits) * 100, 1) : 0;
    $rejectionRate      = $totalPermits > 0 ? round(($rejectedPermits->count() / $totalPermits) * 100, 1) : 0;

    $fullName = trim(
        (Auth::user()->profile?->first_name ?? '') . ' ' .
        (Auth::user()->profile?->middle_name ? strtoupper(substr(Auth::user()->profile->middle_name, 0, 1)) . '. ' : '') .
        (Auth::user()->profile?->last_name ?? '') . ' ' .
        (Auth::user()->profile?->suffix ?? '')
    );

    // Recent 5 permits (latest first)
    $recentPermits = $permits->sortByDesc('created_at')->take(5);
@endphp

<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="row mb-5">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">
                        Hello, <span class="text-primary">{{ $fullName ?: 'Student' }}</span>!
                    </h4>
                    <p class="mb-0 text-muted">Track your event permit applications and performance</p>
                </div>
                <div>
                    <a href="{{ route('student.permits.create') ?? '#' }}" class="btn btn-primary">
                        <i class="ti ti-plus ti-xs me-1"></i> New Permit
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-5">

        {{-- Total Submissions --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Total Submissions</p>
                            <h4 class="mb-0">{{ $totalPermits }}</h4>
                            <small class="text-muted">All your applications</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-primary">
                                <i class="ti ti-file-text ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pending Review --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Pending Review</p>
                            <h4 class="mb-0 text-warning">{{ $pendingPermits->count() }}</h4>
                            <small class="text-muted">Awaiting approval</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-warning">
                                <i class="ti ti-clock ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved Permits --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Approved</p>
                            <h4 class="mb-0 text-success">{{ $approvedPermits->count() }}</h4>
                            <small class="text-muted">Ready to execute</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-success">
                                <i class="ti ti-circle-check ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ongoing Events --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Ongoing Events</p>
                            <h4 class="mb-0 text-info">{{ $ongoingEvents->count() }}</h4>
                            <small class="text-muted">Currently running</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-info">
                                <i class="ti ti-player-play ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="row g-4 mb-5">

        {{-- Permit Status Overview (Bar Chart) --}}
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Permit Application Status</h5>
                    <small class="text-muted">{{ $totalPermits }} total submissions</small>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval Rate (Doughnut with Center Text) --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Overall Approval Rate</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4 position-relative">
                        <canvas id="approvalRateChart" width="220" height="220"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h2 class="mb-0 text-primary fw-bold">{{ $approvalRate }}%</h2>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                    <div class="row w-100 text-center mt-3">
                        <div class="col-6 border-end">
                            <h5 class="mb-0 text-success">{{ $approvedPermits->count() }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-0 text-danger">{{ $rejectedPermits->count() }}</h5>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recent Permits Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Permit Applications</h5>
                    <a href="{{ route('student.permits.index') ?? '#' }}" class="btn btn-sm btn-label-primary">
                        View All <i class="ti ti-arrow-right ti-xs ms-1"></i>
                    </a>
                </div>
                <div class="card-datatable table-responsive">
                    @if($recentPermits->count() > 0)
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Activity Title</th>
                                <th>Type</th>
                                <th>Date Submitted</th>
                                <th>Status</th>
                                <th>Event Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPermits as $permit)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($permit->title_activity ?? 'P', 0, 2)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="fw-medium d-block">{{ Str::limit($permit->title_activity ?? 'Untitled', 28) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td><small class="text-muted">{{ ucfirst($permit->type ?? 'N/A') }}</small></td>
                                <td><small>{{ $permit->created_at?->format('M d, Y') }}</small></td>
                                <td>
                                    @switch($permit->status)
                                        @case('approved')
                                            <span class="badge bg-label-success"><i class="ti ti-check ti-xs"></i> Approved</span>
                                            @break
                                        @case('pending')
                                            <span class="badge bg-label-warning"><i class="ti ti-clock ti-xs"></i> Pending</span>
                                            @break
                                        @case('rejected')
                                            <span class="badge bg-label-danger"><i class="ti ti-x ti-xs"></i> Rejected</span>
                                            @break
                                        @default
                                            <span class="badge bg-label-secondary">Unknown</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($permit->event_status)
                                        @switch($permit->event_status)
                                            @case('ongoing')   <span class="badge bg-label-info">Ongoing</span> @break
                                            @case('successful')<span class="badge bg-label-success">Successful</span> @break
                                            @case('canceled')  <span class="badge bg-label-danger">Canceled</span> @break
                                            @default           <span class="badge bg-label-secondary">{{ ucfirst($permit->event_status) }}</span>
                                        @endswitch
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-6">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ti ti-file-off ti-lg"></i>
                            </span>
                        </div>
                        <h5>No permits yet</h5>
                        <p class="text-muted">Start by creating your first event permit.</p>
                        <a href="{{ route('student.permits.create') ?? '#' }}" class="btn btn-primary mt-3">
                            <i class="ti ti-plus"></i> Create Permit
                        </a>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Bar Chart - Permit Status Distribution
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Pending', 'Approved', 'Rejected'],
                datasets: [{
                    label: 'Permits',
                    data: [{{ $pendingPermits->count() }}, {{ $approvedPermits->count() }}, {{ $rejectedPermits->count() }}],
                    backgroundColor: ['rgba(255, 171, 0, 0.8)', 'rgba(113, 221, 55, 0.8)', 'rgba(255, 62, 29, 0.8)'],
                    borderColor: ['rgb(255, 171, 0)', 'rgb(113, 221, 55)', 'rgb(255, 62, 29)'],
                    borderWidth: 1,
                    borderRadius: 8,
                    borderSkipped: false,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    // Doughnut Chart - Approval Rate
    const rateCtx = document.getElementById('approvalRateChart');
    if (rateCtx) {
        new Chart(rateCtx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [{{ $approvedPermits->count() }}, {{ $rejectedPermits->count() }}, {{ $pendingPermits->count() }}],
                    backgroundColor: ['#71dd37', '#ff3e1d', '#ffab00'],
                    borderWidth: 4,
                    borderColor: '#fff',
                }]
            },
            options: {
                cutout: '78%',
                responsive: true,
                plugins: { legend: { display: false } }
            }
        });
    }
});
</script>
@endsection
