{{-- resources/views/student/permit/pending.blade.php --}}
@extends('layouts.contentNavbarLayout')
@section('title', 'Pending Permits')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active text-warning">Pending Permits</li>
        </ol>
    </nav>

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Permits /</span> Pending Approval
    </h4>

    <div class="row">
        @forelse($permits as $permit)
            @php
                // CORRECT ORDER — Fixed forever
                $steps = [
                    ['name' => 'Faculty Adviser',   'role' => 'Faculty_Adviser'],
                    ['name' => 'BARGO',             'role' => 'BARGO'],
                    ['name' => 'SDSO Head',         'role' => 'SDSO_Head'],
                    ['name' => 'SAS Director',      'role' => 'SAS_Director'],
                    ['name' => 'VP for SAS',        'role' => 'VP_SAS'],
                ];

                $currentStepIndex = 0;
                foreach ($steps as $index => $step) {
                    $approval = $permit->approvals->firstWhere('approver_role', $step['role']);
                    if (!$approval || $approval->status !== 'approved') {
                        $currentStepIndex = $index;
                        break;
                    }
                }
                if ($currentStepIndex == count($steps)) $currentStepIndex = count($steps) - 1;
            @endphp

            <div class="col-12 mb-4">
                <div class="card border-left-warning shadow-sm hover-lift rounded-3">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 fw-bold text-dark">
                                    {{ $permit->title_activity }}
                                </h5>
                                <p class="text-muted mb-1">
                                    <i class="bx bx-calendar me-2"></i>
                                    {{ $permit->date_start ? \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') : '—' }}
                                    @if($permit->date_end)
                                        → {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}
                                    @endif
                                    <span class="mx-2">•</span>
                                    <i class="bx bx-time-five me-1"></i>
                                    @php
                                        $start = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                                        $end   = $permit->time_end   ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A')   : '?';
                                    @endphp
                                    {{ $start }} - {{ $end }}
                                </p>
                            </div>
                            <span class="badge bg-label-warning px-3 py-2">
                                In Progress
                            </span>
                        </div>

                        <!-- Approval Timeline -->
                        <div class="approval-timeline mb-4">
                            <div class="timeline-steps">
                                @foreach($steps as $index => $step)
                                    @php
                                        $approval = $permit->approvals->firstWhere('approver_role', $step['role']);
                                        $isPassed   = $approval && $approval->status === 'approved';
                                        $isCurrent  = $index === $currentStepIndex && !$isPassed;
                                        $isFuture   = $index > $currentStepIndex;
                                    @endphp

                                    <div class="timeline-step {{ $isFuture ? 'future' : '' }}">
                                        <div class="step-connector {{ $isPassed ? 'passed' : '' }}"></div>

                                        <div class="step-circle {{ $isPassed ? 'passed' : ($isCurrent ? 'current' : 'future') }}">
                                            @if($isPassed)
                                                <i class="bx bx-check fs-4"></i>
                                            @elseif($isCurrent)
                                                <i class="bx bx-loader-alt bx-spin fs-5"></i>
                                            @else
                                                <span class="step-number">{{ $index + 1 }}</span>
                                            @endif
                                        </div>

                                        <div class="step-content">
                                            <div class="step-title {{ $isPassed ? 'text-success' : ($isCurrent ? 'text-warning fw-bold' : 'text-muted') }}">
                                                {{ $step['name'] }}
                                            </div>
                                            <small class="text-muted fs-xs">
                                                @if($isPassed)
                                                    Approved
                                                @elseif($isCurrent)
                                                    <span class="text-warning">In Review</span>
                                                @else
                                                    Awaiting
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Current Status -->
                        <div class="text-center my-4">
                            <small class="text-muted">
                                Currently waiting for approval from:<br>
                                <strong class="text-warning fw-bold">{{ $steps[$currentStepIndex]['name'] }}</strong>
                                <span class="text-muted">(Step {{ $currentStepIndex + 1 }} of {{ count($steps) }})</span>
                            </small>
                        </div>

                        <!-- View Details Button -->
                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="button" class="btn btn-outline-warning rounded-pill px-4"
                                    data-bs-toggle="modal" data-bs-target="#pendingModal{{ $permit->permit_id }}">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SLEEK MATERIO-STYLE MODAL --}}
            <div class="modal fade" id="pendingModal{{ $permit->permit_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content shadow-lg border-0 overflow-hidden">
                        <div class="modal-header bg-gradient-warning text-white border-0 py-4">
                            <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                                Permit Details #{{ $permit->hashed_id ?? $permit->permit_id }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                        </div>

                        <div class="modal-body p-5">
                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-warning mb-3">{{ $permit->title_activity }}</h6>
                                    <div class="d-flex gap-3 flex-wrap mb-4">
                                        <span class="badge bg-label-warning">
                                            Pending Approval
                                        </span>
                                        <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }}">
                                            {{ $permit->type }}
                                        </span>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-target text-warning fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Purpose</small>
                                            <p class="mb-0">{{ $permit->purpose ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-category text-info fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Nature</small>
                                            <p class="mb-0">{{ $permit->nature ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-map text-danger fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Venue</small>
                                            <p class="mb-0 fw-semibold">{{ $permit->venue }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-calendar-event text-warning fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Date</small>
                                            <p class="mb-0 fw-bold text-warning">
                                                {{ $permit->date_start ? \Carbon\Carbon::parse($permit->date_start)->format('F d, Y') : '—' }}
                                                @if($permit->date_end) → {{ \Carbon\Carbon::parse($permit->date_end)->format('F d, Y') }} @endif
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-time-five text-primary fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Time</small>
                                            <p class="mb-0 fw-semibold">{{ $start }} – {{ $end }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-group text-info fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Participants</small>
                                            <p class="mb-0">{{ $permit->participants ?? '—' }}</p>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-user-plus text-success fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Expected Attendees</small>
                                            <p class="mb-0 fw-bold">{{ $permit->number ?? '—' }} persons</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="modal-footer border-0 bg-light px-5 py-4">
                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
           
        @endforelse
    </div>

    <div class="mt-5">
        {{ $permits->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('page-style')
<style>
    .hover-lift:hover { transform: translateY(-6px); box-shadow: 0 15px 35px rgba(0,0,0,0.12)!important; transition: all 0.3s ease; }
    .border-left-warning { border-left: 5px solid #ff9f43 !important; }

    .approval-timeline { padding: 20px 0; }
    .timeline-steps { display: flex; justify-content: space-between; position: relative; overflow-x: auto; padding: 15px 0; }
    .timeline-step { display: flex; flex-direction: column; align-items: center; min-width: 140px; position: relative; z-index: 1; }
    .step-connector { position: absolute; top: 30px; left: 50%; right: -50%; height: 4px; background: #e0e0e0; z-index: 0; }
    .timeline-step:first-child .step-connector { left: 50%; }
    .timeline-step:last-child .step-connector { display: none; }
    .step-connector.passed { background: #28c76f; }

    .step-circle { width: 60px; height: 60px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 4px 15px rgba(0,0,0,0.15); transition: all 0.3s ease; }
    .step-circle.passed { background: #28c76f; color: white; border: 4px solid #69e49a; }
    .step-circle.current { background: #ff9f43; color: white; border: 4px solid #ffc107; animation: pulse 2s infinite; }
    .step-circle.future { background: #f8f9fa; color: #a8a8b3; border: 3px dashed #e0e0e0; }
    .step-number { font-size: 1.4rem; font-weight: 600; }

    @keyframes pulse {
        0% { box-shadow: 0 0 0 0 rgba(255, 159, 67, 0.4); }
        70% { box-shadow: 0 0 0 12px rgba(255, 159, 67, 0); }
        100% { box-shadow: 0 0 0 0 rgba(255, 159, 67, 0); }
    }
</style>
@endsection
