{{-- resources/views/adviser/dashboard.blade.php --}}
@extends('layouts.adviserLayout')

@section('title', 'Adviser Dashboard')

@section('page-style')
<style>
    .stat-card {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 0.5rem 1.5rem rgba(67, 89, 113, 0.15) !important;
    }
    .stat-icon {
        width: 42px;
        height: 42px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0.375rem;
    }
    .chart-container {
        position: relative;
        height: 300px;
    }
    @media (max-width: 768px) {
        .chart-container {
            height: 250px;
        }
    }
</style>
@endsection

@section('content')
@php
  use Carbon\Carbon;
   $now      = Carbon::now();
    $today    = $now->format('Y-m-d');
    $timeNow  = $now->format('H:i:s');
    $hour = $now->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

    $orgIds = Auth::user()->advisedOrganizations()->pluck('organization_id');

    // Total permits
    $totalPermits = \DB::table('permits')->whereIn('organization_id', $orgIds)->count();

    // Pending review (where Faculty_Adviser approval is pending)
    $pendingReview = \DB::table('permits')
        ->whereIn('organization_id', $orgIds)
        ->whereExists(function($q) {
            $q->from('event_approval_flow')
              ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
              ->where('approver_role', 'Faculty_Adviser')
              ->where('status', 'pending');
        })
        ->count();

    // Approved by you (Faculty_Adviser approved)
    $approvedByYou = \DB::table('permits')
        ->whereIn('organization_id', $orgIds)
        ->whereExists(function($q) {
            $q->from('event_approval_flow')
              ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
              ->where('approver_role', 'Faculty_Adviser')
              ->where('status', 'approved');
        })
        ->count();

    // Fully approved (all 5 roles approved)
    $fullyApproved = \DB::table('permits')
        ->whereIn('organization_id', $orgIds)
        ->whereExists(function($q) {
            $q->select(\DB::raw(1))
              ->from('event_approval_flow')
              ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
              ->groupBy('permit_id')
              ->havingRaw('COUNT(*) >= 5 AND SUM(status = "approved") = 5');
        })
        ->count();

    // Rejected
    $rejectedPermits = \DB::table('permits')
        ->whereIn('organization_id', $orgIds)
        ->whereExists(function($q) {
            $q->from('event_approval_flow')
              ->whereColumn('event_approval_flow.permit_id', 'permits.permit_id')
              ->where('status', 'rejected');
        })
        ->count();

    // Recent permits (last 5)
    $recentPermits = \DB::table('permits')
        ->join('organizations', 'permits.organization_id', '=', 'organizations.organization_id')
        ->leftJoin('event_approval_flow', function($join) {
            $join->on('permits.permit_id', '=', 'event_approval_flow.permit_id')
                 ->where('event_approval_flow.approver_role', '=', 'Faculty_Adviser');
        })
        ->whereIn('permits.organization_id', $orgIds)
        ->select(
            'permits.*',
            'organizations.organization_name',
            'event_approval_flow.status as adviser_status'
        )
        ->orderBy('permits.created_at', 'desc')
        ->limit(5)
        ->get();

    // Monthly statistics (last 6 months)
    $monthlyStats = \DB::table('permits')
        ->whereIn('organization_id', $orgIds)
        ->where('created_at', '>=', now()->subMonths(6))
        ->selectRaw('MONTH(created_at) as month, COUNT(*) as count')
        ->groupBy('month')
        ->orderBy('month')
        ->pluck('count', 'month')
        ->toArray();

    // Approval rate
    $approvalRate = $totalPermits > 0 ? round(($fullyApproved / $totalPermits) * 100, 1) : 0;
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
                            <h5 class="text-white fw-semibold mb-1">Organization Adviser Review Dashboard</h5>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">
        {{-- Total Permits --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Total Permits</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2">{{ $totalPermits }}</h4>
                            </div>
                            <small class="text-muted">All time</small>
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
            <a href="{{ route('adviser.approvals') }}" class="text-decoration-none">
                <div class="d-flex justify-content-between">
                    <div class="card-info">
                        <p class="card-text mb-1">Pending Review</p>
                        <div class="d-flex align-items-center mb-1">
                            <h4 class="mb-0 me-2 text-warning">{{ $pendingReview }}</h4>
                            @if($pendingReview > 0)
                                <span class="badge bg-label-warning">Action Required</span>
                            @endif
                        </div>
                        <small class="text-muted">Awaiting your approval</small>
                    </div>
                    <div class="card-icon">
                        <span class="stat-icon bg-label-warning">
                            <i class="ti ti-clock ti-md"></i>
                        </span>
                    </div>
                </div>
            </a>
        </div>
    </div>
</div>

        {{-- Approved by You --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                   <a href="{{ route('adviser.history') }}" class="text-decoration-none">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Approved by You</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2 text-info">{{ $approvedByYou }}</h4>
                            </div>
                            <small class="text-muted">Your approved permits</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-info">
                                <i class="ti ti-check ti-md"></i>
                            </span>
                        </div>
                    </div>
                  </a>
                </div>
            </div>
        </div>

        {{-- Fully Approved --}}
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Fully Approved</p>
                            <div class="d-flex align-items-center mb-1">
                                <h4 class="mb-0 me-2 text-success">{{ $fullyApproved }}</h4>
                            </div>
                            <small class="text-muted">All approvals done</small>
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
    </div>

    {{-- Charts & Recent Activity --}}
    <div class="row g-4 mb-4">
        {{-- Approval Overview Chart --}}
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Permit Status Overview</h5>
                    <div class="dropdown">
                        <button class="btn btn-sm btn-text-secondary rounded-pill btn-icon dropdown-toggle hide-arrow" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="ti ti-dots-vertical ti-sm"></i>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="{{ route('adviser.permits.index') }}">View Details</a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approval Rate --}}
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Approval Rate</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4">
                        <div class="position-relative">
                            <canvas id="approvalRateChart" width="200" height="200"></canvas>
                            <div class="position-absolute top-50 start-50 translate-middle text-center">
                                <h2 class="mb-0 text-primary">{{ $approvalRate }}%</h2>
                                <small class="text-muted">Success Rate</small>
                            </div>
                        </div>
                    </div>
                    <div class="row w-100 text-center">
                        <div class="col-6 border-end">
                            <h5 class="mb-0 text-success">{{ $fullyApproved }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-0 text-danger">{{ $rejectedPermits }}</h5>
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
                    <h5 class="card-title mb-0">Recent Permit Requests</h5>
                    <a href="{{ route('adviser.permits.index') }}" class="btn btn-sm btn-label-primary">
                        View All
                        <i class="ti ti-arrow-right ti-xs ms-1"></i>
                    </a>
                </div>
                <div class="card-datatable table-responsive">
                    @if($recentPermits->count() > 0)
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Organization</th>
                                <th>Activity</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPermits as $permit)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-2">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($permit->organization_name, 0, 2)) }}
                                            </span>
                                        </div>
                                        <span class="fw-medium">{{ $permit->organization_name }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-medium d-block">{{ Str::limit($permit->title_activity, 30) }}</span>
                                        <small class="text-muted">{{ $permit->type }}</small>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') }}
                                    </small>
                                </td>
                                <td>
                                    @if($permit->adviser_status === 'approved')
                                        <span class="badge bg-label-success">
                                            <i class="ti ti-check ti-xs me-1"></i>Approved
                                        </span>
                                    @elseif($permit->adviser_status === 'rejected')
                                        <span class="badge bg-label-danger">
                                            <i class="ti ti-x ti-xs me-1"></i>Rejected
                                        </span>
                                    @else
                                        <span class="badge bg-label-warning">
                                            <i class="ti ti-clock ti-xs me-1"></i>Pending
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('adviser.permits.index', $permit->permit_id) }}"
                                       class="btn btn-sm btn-icon btn-text-secondary rounded-pill">
                                        <i class="ti ti-eye ti-sm"></i>
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @else
                    <div class="text-center py-5">
                        <div class="avatar avatar-lg mx-auto mb-3">
                            <span class="avatar-initial rounded-circle bg-label-secondary">
                                <i class="ti ti-file-off ti-lg"></i>
                            </span>
                        </div>
                        <h5 class="mb-1">No Recent Permits</h5>
                        <p class="text-muted mb-0">There are no permit requests yet.</p>
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
document.addEventListener('DOMContentLoaded', function() {
    // Status Overview Chart (Bar Chart)
    const statusCtx = document.getElementById('statusChart');
    if (statusCtx) {
        new Chart(statusCtx, {
            type: 'bar',
            data: {
                labels: ['Pending Review', 'Approved by You', 'Fully Approved', 'Rejected'],
                datasets: [{
                    label: 'Permits',
                    data: [{{ $pendingReview }}, {{ $approvedByYou }}, {{ $fullyApproved }}, {{ $rejectedPermits }}],
                    backgroundColor: [
                        'rgba(255, 171, 0, 0.8)',   // Warning
                        'rgba(3, 195, 236, 0.8)',   // Info
                        'rgba(113, 221, 55, 0.8)',  // Success
                        'rgba(255, 62, 29, 0.8)'    // Danger
                    ],
                    borderColor: [
                        'rgb(255, 171, 0)',
                        'rgb(3, 195, 236)',
                        'rgb(113, 221, 55)',
                        'rgb(255, 62, 29)'
                    ],
                    borderWidth: 1,
                    borderRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(67, 89, 113, 0.9)',
                        padding: 12,
                        cornerRadius: 8
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1,
                            color: '#697a8d'
                        },
                        grid: {
                            color: 'rgba(67, 89, 113, 0.1)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#697a8d'
                        },
                        grid: {
                            display: false
                        }
                    }
                }
            }
        });
    }

    // Approval Rate Chart (Doughnut)
    const rateCtx = document.getElementById('approvalRateChart');
    if (rateCtx) {
        new Chart(rateCtx, {
            type: 'doughnut',
            data: {
                labels: ['Approved', 'Rejected', 'Pending'],
                datasets: [{
                    data: [
                        {{ $fullyApproved }},
                        {{ $rejectedPermits }},
                        {{ $totalPermits - $fullyApproved - $rejectedPermits }}
                    ],
                    backgroundColor: [
                        'rgba(113, 221, 55, 0.8)',
                        'rgba(255, 62, 29, 0.8)',
                        'rgba(255, 171, 0, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 3
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                cutout: '75%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        backgroundColor: 'rgba(67, 89, 113, 0.9)',
                        padding: 12,
                        cornerRadius: 8
                    }
                }
            }
        });
    }
});
</script>
@endsection
