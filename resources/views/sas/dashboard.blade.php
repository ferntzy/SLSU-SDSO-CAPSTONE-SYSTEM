{{-- resources/views/bargo/dashboard.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'SAS Dashboard')

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
    .action-card {
        transition: all 0.3s ease;
    }
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 24px rgba(67, 89, 113, 0.2) !important;
    }
    .card {
        box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        border: none;
        border-radius: 0.5rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    @php
        $now = now()->setTimezone('Asia/Manila');
        $hour = $now->hour;
        $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');

        $thisMonth = now()->startOfMonth();
        $approvedThisMonth = \App\Models\EventApprovalFlow::where('approver_role', 'SAS_Director')
            ->where('status', 'approved')
            ->where('updated_at', '>=', $thisMonth)
            ->count();

        $rejectedThisMonth = \App\Models\EventApprovalFlow::where('approver_role', 'SAS_Director')
            ->where('status', 'rejected')
            ->where('updated_at', '>=', $thisMonth)
            ->count();

        $approvalRate = $approvedThisMonth + $rejectedThisMonth > 0
            ? round(($approvedThisMonth / ($approvedThisMonth + $rejectedThisMonth)) * 100, 1)
            : 0;

      $approvedByYou = \DB::table('event_approval_flow')
    ->where('approver_role', 'SAS_Director')
    ->where('status', 'approved')
    ->count();
    @endphp

    <!-- Header Section -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-primary text-white" style="background: linear-gradient(135deg, #696cff 0%, #8b8dff 100%);">
                <div class="card-body">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="text-white mb-2 fw-semibold">{{ $greeting }}, {{ Auth::user()->profile?->first_name }}</h4>
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
                            <h5 class="text-white fw-semibold mb-1">SAS Review Dashboard</h5>

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
            <div class="card stat-card">
                <div class="card-body">
                    <a href="{{ route('sas.pending') }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Pending Review</p>
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="mb-0 me-2 text-warning">{{ $pendingReviews }}</h4>
                                    @if($pendingReviews > 0)
                                        <span class="badge bg-label-warning">Action Required</span>
                                    @endif
                                </div>
                                <small class="text-muted">Awaiting Your Review</small>
                            </div>
                            <div class="card-icon">
                                <span class="stat-icon bg-label-warning">
                                    <i class="mdi mdi-clock-alert-outline mdi-24px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        {{-- Approved by You --}}
        {{-- <div class="col-xl-3 col-sm-6">
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
        </div> --}}

        <!-- Approved -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <a href="{{ route('sas.approved') }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Approved</p>
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="mb-0 me-2 text-success">{{ $approved }}</h4>
                                </div>
                                <small class="text-muted">Forwarded to SDSO Head</small>
                            </div>
                            <div class="card-icon">
                                <span class="stat-icon bg-label-success">
                                    <i class="mdi mdi-check-bold mdi-24px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Rejected -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <a href="{{ route('sas.rejected') }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Rejected</p>
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="mb-0 me-2 text-danger">{{ $rejected }}</h4>
                                </div>
                                <small class="text-muted">Returned to Organization</small>
                            </div>
                            <div class="card-icon">
                                <span class="stat-icon bg-label-danger">
                                    <i class="mdi mdi-close-thick mdi-24px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>

        <!-- Total Processed -->
        <div class="col-xl-3 col-sm-6">
            <div class="card stat-card">
                <div class="card-body">
                    <a href="{{ route('sas.history') }}" class="text-decoration-none">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Total Processed</p>
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="mb-0 me-2 text-primary">{{ $approved + $rejected }}</h4>
                                </div>
                                <small class="text-muted">Your Total Actions</small>
                            </div>
                            <div class="card-icon">
                                <span class="stat-icon bg-label-primary">
                                    <i class="mdi mdi-counter mdi-24px"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics -->
    <div class="row g-4 mb-4">
        <!-- Performance Overview Chart -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-chart-line text-primary me-2"></i>
                        SAS Performance This Month
                    </h5>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="performanceChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Approval Rate -->
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
                            <h5 class="mb-0 text-success">{{ $approvedThisMonth }}</h5>
                            <small class="text-muted">Approved</small>
                        </div>
                        <div class="col-6">
                            <h5 class="mb-0 text-danger">{{ $rejectedThisMonth }}</h5>
                            <small class="text-muted">Rejected</small>
                        </div>
                    </div>
                    <div class="mt-3 w-100">
                        <small class="text-muted d-block text-center">Healthy Range: 85–95%</small>
                    </div>
                </div>
            </div>
        </div>
    </div>




    </div>

    <!-- Information Footer -->

</div>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Performance Overview Chart (Bar Chart)
    const performanceCtx = document.getElementById('performanceChart');
    if (performanceCtx) {
        new Chart(performanceCtx, {
            type: 'bar',
            data: {
                labels: ['Pending Review', 'Approved By You',  'Approved', 'Rejected'],
                datasets: [{
                    label: 'Permits',
                    data: [{{ $pendingReviews }},{{ $approvedByYou }}, {{ $approved }}, {{ $rejected }}],
                    backgroundColor: [
                        'rgba(255, 171, 0, 0.8)',   // Warning
                         'rgba(3, 195, 236, 0.8)', // Info
                        'rgba(113, 221, 55, 0.8)',  // Success
                        'rgba(255, 62, 29, 0.8)',    // Danger

                    ],
                    borderColor: [
                        'rgb(255, 171, 0)',
                         'rgba(3, 195, 236, 0.8)',   // Info
                        'rgb(113, 221, 55)',
                        'rgb(255, 62, 29)',

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
                        {{ $approvedThisMonth }},
                        {{ $rejectedThisMonth }},
                        {{ $pendingReviews }}
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
