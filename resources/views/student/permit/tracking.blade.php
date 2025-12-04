{{-- resources/views/student/dashboard.blade.php --}}
@php
    use Carbon\Carbon;

    // Fix the undefined $now + add truly accurate ongoing logic
    $now      = Carbon::now();
    $today    = $now->format('Y-m-d');
    $timeNow  = $now->format('H:i:s');
    $hour = $now->hour;
    $greeting = $hour < 12 ? 'Good Morning' : ($hour < 17 ? 'Good Afternoon' : 'Good Evening');
    // YOUR ORIGINAL DATA LOGIC — STILL UNTOUCHED
    $container = 'container-xxl';

    $ongoingEvents     = $ongoingEvents ?? collect();
    $successfulEvents  = $successfulEvents ?? collect();
    $canceledEvents    = $canceledEvents ?? collect();



    $totalPermits   = $pendingPermits->count() + $approvedPermits->count() + $rejectedPermits->count();
    $approvalRate   = $totalPermits > 0 ? round(($approvedPermits->count() / $totalPermits) * 100, 1) : 0;
    $recentPermits  = collect([$pendingPermits, $approvedPermits, $rejectedPermits])
                        ->flatten()->sortByDesc('created_at')->take(5);

    // ————————————————————————————————————————————————
    // ACCURATE ONGOING EVENTS COUNT (respects time_end!)
    // ————————————————————————————————————————————————
    $trulyOngoing = $approvedPermits->filter(function ($permit) use ($now, $today) {
        if (!$permit->date_start) {
            return false;
        }

        $startDate = Carbon::parse($permit->date_start)->format('Y-m-d');
        $endDate   = $permit->date_end
            ? Carbon::parse($permit->date_end)->format('Y-m-d')
            : $startDate;

        // Is today within the event's date range?
        if ($today < $startDate || $today > $endDate) {
            return false;
        }

        // Build full datetime for start and end
        $eventStart = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $startDate . ' ' . ($permit->time_start ?? '00:00:00')
        );

        $eventEnd = Carbon::createFromFormat(
            'Y-m-d H:i:s',
            $endDate . ' ' . ($permit->time_end ?? '23:59:59')
        );

        // Is NOW between start and end?
        return $now->between($eventStart, $eventEnd);
    });

    // Override the old inaccurate count
    $ongoingEvents = $trulyOngoing;
    // ————————————————————————————————————————————————
@endphp

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
<div class="{{ $container }} flex-grow-1 container-p-y">

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
                            <h5 class="text-white fw-semibold mb-1">STUDENT Dashboard</h5>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-5">
        <div class="col-xl-3 col-sm-6">
          <a href="{{ route('student.submissions.history') }}" class="text-decoration-none">
            <div class="card stat-card">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="card-info">
                            <p class="card-text mb-1">Total Submissions</p>
                            <h4 class="mb-0">{{ $totalPermits }}</h4>
                            <small class="text-muted">All applications</small>
                        </div>
                        <div class="card-icon">
                            <span class="stat-icon bg-label-primary">
                                <i class="ti ti-file-text ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
          </a>
        </div>

        <!-- Pending Card -->
        <div class="col-xl-3 col-sm-6">
            <a href="{{ url('/student/page/pending-permits') }}" class="text-decoration-none">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Pending</p>
                                <h4 class="mb-0 text-warning">{{ $pendingPermits->count() }}</h4>
                                <small class="text-muted">Under review</small>
                            </div>
                            <span class="stat-icon bg-label-warning">
                                <i class="ti ti-clock ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Approved Card -->
        <div class="col-xl-3 col-sm-6">
            <a href="{{ url('/student/page/approved-permits') }}" class="text-decoration-none">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Approved</p>
                                <h4 class="mb-0 text-success">{{ $approvedPermits->count() }}</h4>
                                <small class="text-muted">Ready to go</small>
                            </div>
                            <span class="stat-icon bg-label-success">
                                <i class="ti ti-circle-check ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        <!-- Ongoing Card -->
        <div class="col-xl-3 col-sm-6">
            <a href="{{ route('student.ongoing') }}" class="text-decoration-none">
                <div class="card stat-card h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div class="card-info">
                                <p class="card-text mb-1">Ongoing Events</p>
                                <h4 class="mb-0 text-info">{{ $ongoingEvents->count() }}</h4>
                                <small class="text-muted">Happening right now</small>
                            </div>
                            <span class="stat-icon bg-label-info">
                                <i class="ti ti-player-play ti-md"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    {{-- Rest of your dashboard (charts, recent table, etc.) — 100% unchanged --}}
    <div class="row g-4 mb-5">
        <div class="col-lg-8">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Permit Status Overview</h5>
                    <small class="text-muted">{{ $totalPermits }} total</small>
                </div>
                <div class="card-body">
                    <div class="chart-container">
                        <canvas id="statusChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">Approval Rate</h5>
                </div>
                <div class="card-body d-flex flex-column justify-content-center align-items-center">
                    <div class="mb-4 position-relative">
                        <canvas id="approvalRateChart" width="220" height="220"></canvas>
                        <div class="position-absolute top-50 start-50 translate-middle text-center">
                            <h2 class="mb-0 text-primary fw-bold">{{ $approvalRate }}%</h2>
                            <small class="text-muted">Approved</small>
                        </div>
                    </div>
                    <div class="row w-100 text-center">
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

    {{-- Recent Applications Table --}}
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Recent Applications</h5>
                </div>
                <div class="card-datatable table-responsive">
                    @if($recentPermits->count())
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Activity</th>
                                <th>Type</th>
                                <th>Submitted</th>
                                <th>Status</th>
                                <th>Event</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($recentPermits as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm me-3">
                                            <span class="avatar-initial rounded-circle bg-label-primary">
                                                {{ strtoupper(substr($p->title_activity ?? 'P', 0, 2)) }}
                                            </span>
                                        </div>
                                        <span class="fw-medium">{{ Str::limit($p->title_activity ?? 'Untitled', 30) }}</span>
                                    </div>
                                </td>
                                <td><small class="text-muted">{{ ucfirst($p->type ?? '-') }}</small></td>
                                <td><small>{{ $p->created_at?->format('M d, Y') }}</small></td>
                                <td>
                                    @switch($p->status)
                                        @case('approved')  <span class="badge bg-label-success">Approved</span> @break
                                        @case('pending')   <span class="badge bg-label-warning">Pending</span> @break
                                        @case('rejected')  <span class="badge bg-label-danger">Rejected</span> @break
                                        @default           <span class="badge bg-label-secondary">{{ ucfirst($p->status) }}</span>
                                    @endswitch
                                </td>
                                <td>
                                    @if($p->event_status)
                                        <span class="badge bg-label-{{ $p->event_status === 'ongoing' ? 'info' : ($p->event_status === 'successful' ? 'success' : 'danger') }}">
                                            {{ ucfirst($p->event_status) }}
                                        </span>
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
                        <i class="ti ti-file-off ti-lg text-muted mb-3"></i>
                        <h5>No applications yet</h5>
                        <p class="text-muted">Submit your first permit to get started!</p>
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
    new Chart(document.getElementById('statusChart'), {
        type: 'bar',
        data: {
            labels: ['Pending', 'Approved', 'Rejected'],
            datasets: [{
                data: [{{ $pendingPermits->count() }}, {{ $approvedPermits->count() }}, {{ $rejectedPermits->count() }}],
                backgroundColor: ['rgba(255, 171, 0, 0.8)', 'rgba(113, 221, 55, 0.8)', 'rgba(255, 62, 29, 0.8)'],
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
        }
    });

    new Chart(document.getElementById('approvalRateChart'), {
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
});
</script>
@endsection
