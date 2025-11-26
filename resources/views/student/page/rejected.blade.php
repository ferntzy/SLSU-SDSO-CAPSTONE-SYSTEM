{{-- resources/views/student/pages/rejected.blade.php --}}
@extends('layouts.contentNavbarLayout')
@section('title', 'Rejected Permits')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active text-danger">Rejected Permits</li>
        </ol>
    </nav>

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Permits /</span> Rejected
    </h4>

    <div class="row">
        @forelse($permits as $permit)
            @php
                $rejectApproval = $permit->approvals->where('status', 'rejected')->first();
                $rejectedBy = $rejectApproval?->approver?->name ?? $rejectApproval?->approver_role ?? 'Unknown';
                $rejectedReason = $rejectApproval?->remarks ?? 'No reason provided.';
                $rejectedAt = $rejectApproval?->updated_at?->format('M d, Y \a\t g:i A') ?? '—';

                $start = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                $end = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
            @endphp

            <div class="col-12 mb-4">
                <div class="card border-left-danger shadow-sm hover-lift rounded-3">
                    <div class="card-body p-4">

                        <!-- Header -->
                        <div class="d-flex justify-content-between align-items-start mb-4">
                            <div class="flex-grow-1">
                                <h5 class="card-title mb-2 fw-bold text-dark">{{ $permit->title_activity }}</h5>
                                <p class="text-muted mb-1">
                                    <i class="bx bx-calendar me-2"></i>
                                    {{ $permit->date_start ? \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') : '—' }}
                                    @if($permit->date_end) → {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }} @endif
                                    <span class="mx-2">•</span>
                                    <i class="bx bx-time-five me-1"></i>
                                    {{ $start }} - {{ $end }}
                                </p>
                            </div>
                            <span class="badge bg-label-danger px-3 py-2">Rejected</span>
                        </div>

                        <!-- Soft Rejection Summary -->
                        <div class="alert bg-light-danger border-danger border-opacity-20 mb-4">
                            <div class="d-flex gap-3 align-items-start">
                                <i class="bx bx-x-circle fs-3 text-danger mt-1"></i>
                                <div>
                                    <strong class="text-danger">Rejected by {{ $rejectedBy }}</strong>
                                    <p class="mb-1 mt-1">{{ $rejectedReason }}</p>
                                    <small class="text-muted">on {{ $rejectedAt }}</small>
                                </div>
                            </div>
                        </div>

                        <!-- View Details -->
                        <div class="d-flex justify-content-end pt-3 border-top">
                            <button type="button" class="btn btn-outline-danger rounded-pill px-4"
                                    data-bs-toggle="modal"
                                    data-bs-target="#rejectedModal{{ $permit->permit_id }}">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- CLEAN, SLEEK MODAL – NO RED HEADER – 100% SAME AS PENDING & APPROVED --}}
            <div class="modal fade" id="rejectedModal{{ $permit->permit_id }}" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                    <div class="modal-content shadow-lg border-0 overflow-hidden">

                        <!-- CLEAN HEADER – Same as Pending & Approved -->
                        <div class="modal-header border-0 pb-4">
                            <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                <i class="bx bx-file text-danger"></i>
                                Permit Details #{{ $permit->hashed_id ?? $permit->permit_id }}
                            </h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>

                        <div class="modal-body p-5 pt-3">
                            <div class="row g-4">
                                <div class="col-12">
                                    <h6 class="fw-bold text-danger mb-3">{{ $permit->title_activity }}</h6>
                                    <div class="d-flex gap-3 flex-wrap mb-4">
                                        <span class="badge bg-label-danger">Rejected</span>
                                        <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }}">
                                            {{ $permit->type }}
                                        </span>
                                    </div>
                                </div>

                                <hr class="my-4">

                                <!-- Rejection Info – Clean, Soft, Professional -->
                                <div class="col-12 mb-4">
                                    <div class="border border-danger border-opacity-20 rounded-4 p-4 bg-light-danger">
                                        <div class="d-flex align-items-start gap-3">
                                            <i class="bx bx-x-circle text-danger fs-2"></i>
                                            <div>
                                                <h6 class="fw-bold text-danger mb-2">Permit Was Rejected</h6>
                                                <p class="mb-1"><strong>By:</strong> <span class="text-danger fw-bold">{{ $rejectedBy }}</span></p>
                                                <p class="mb-3"><strong>On:</strong> {{ $rejectedAt }}</p>
                                                <div class="p-4 bg-white border rounded-3 shadow-sm">
                                                    <small class="text-muted text-uppercase fw-bold d-block mb-2">Reason / Remarks</small>
                                                    <p class="mb-0 text-dark fw-medium">{{ $rejectedReason }}</p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Identical Details Layout -->
                                <div class="col-md-6">
                                    <div class="d-flex align-items-start gap-3">
                                        <i class="bx bx-target text-muted fs-5"></i>
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
                                        <i class="bx bx-calendar-event text-danger fs-5"></i>
                                        <div>
                                            <small class="text-muted text-uppercase fw-semibold">Date</small>
                                            <p class="mb-0 fw-bold text-danger">
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
                                        <i class="bx bx-user-plus text-danger fs-5"></i>
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
            <div class="col-12 text-center py-6">
                <i class="bx bx-check-circle bx-lg text-success opacity-30"></i>
                <h5 class="mt-4 text-muted">No rejected permits</h5>
                <p class="text-muted">All your submitted permits are either pending or approved.</p>
                <a href="{{ route('student.permit.create') }}" class="btn btn-outline-primary mt-3">
                    Submit New Permit
                </a>
            </div>
        @endforelse
    </div>

    <div class="mt-5">
        {{ $permits->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection

@section('page-style')
<style>
    .hover-lift:hover {
        transform: translateY(-6px);
        box-shadow: 0 15px 35px rgba(0,0,0,0.12) !important;
        transition: all 0.3s ease;
    }
    .border-left-danger { border-left: 5px solid #ff6b6b !important; }
    .bg-light-danger { background-color: rgba(255, 107, 107, 0.08) !important; }
</style>
@endsection
