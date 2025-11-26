{{-- resources/views/student/permit/approved.blade.php --}}
@extends('layouts.contentNavbarLayout')
@section('title', 'Approved Permits')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active text-success">Approved Permits</li>
        </ol>
    </nav>

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Permits /</span> Approved
    </h4>

    <div class="row">
        @forelse($permits as $permit)
            @php
                $vpApproval = $permit->approvals->firstWhere('approver_role', 'VP_SAS');
                $finalDate = $vpApproval?->approved_at;
                $start = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                $end = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
            @endphp

            <div class="col-12 col-lg-6 mb-4">
                <div class="card border-0 shadow-sm hover-lift rounded-4 overflow-hidden">
                    <div class="bg-success text-white py-4 px-4 text-center">
                        <i class="bx bx-check-double fs-1 mb-2 d-block"></i>
                        <h5 class="mb-1 fw-bold">Fully Approved</h5>
                        <small class="opacity-90">
                            Final signature on {{ $finalDate ? \Carbon\Carbon::parse($finalDate)->format('F d, Y') : '—' }}
                        </small>
                    </div>

                    <div class="card-body p-4">
                        <h5 class="fw-bold text-success mb-3">{{ $permit->title_activity }}</h5>

                        <div class="d-flex align-items-start gap-3 text-muted mb-3">
                            <i class="bx bx-calendar-event text-success fs-4 mt-1"></i>
                            <div>
                                <div class="fw-semibold text-dark">
                                    {{ $permit->date_start ? \Carbon\Carbon::parse($permit->date_start)->format('l, F d, Y') : '—' }}
                                    @if($permit->date_end) → {{ \Carbon\Carbon::parse($permit->date_end)->format('F d, Y') }} @endif
                                </div>
                                <small class="text-muted">{{ $start }} – {{ $end }}</small>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="d-flex align-items-center text-muted">
                                <i class="bx bx-map text-danger me-2"></i>
                                <small>{{ Str::limit($permit->venue, 35) }}</small>
                            </div>
                            <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-warning' }} fs-sm">
                                {{ $permit->type }}
                            </span>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex gap-3">
                            <button type="button" class="btn btn-outline-success rounded-pill flex-grow-1"
                                    data-bs-toggle="modal" data-bs-target="#permitModal{{ $permit->permit_id }}">
                                View Details
                            </button>

                            {{-- CORRECT: Simple GET link + download attribute --}}
                            <a href="{{ route('student.permit.view', $permit->hashed_id) }}"
                               class="btn btn-success rounded-pill px-4"
                               download="Permit-{{ $permit->hashed_id }}.pdf">
                               PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SLEEK MATERIO-STYLE MODAL – APPROVED VERSION (Consistent with Pending) --}}
<div class="modal fade" id="permitModal{{ $permit->permit_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 overflow-hidden">

            <!-- Header – Success Theme -->
            <div class="modal-header bg-gradient-success text-white border-0 py-4">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2">
                    Permit Details #{{ $permit->hashed_id ?? $permit->permit_id }}
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-5">
                <div class="row g-4">
                    <div class="col-12">
                        <h6 class="fw-bold text-success mb-3">{{ $permit->title_activity }}</h6>
                        <div class="d-flex gap-3 flex-wrap mb-4">
                            <span class="badge bg-label-success">
                                Fully Approved
                            </span>
                            <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }}">
                                {{ $permit->type }}
                            </span>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="col-md-6">
                        <div class="d-flex align-items-start gap-3">
                            <i class="bx bx-target text-success fs-5"></i>
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
                            <i class="bx bx-calendar-event text-success fs-5"></i>
                            <div>
                                <small class="text-muted text-uppercase fw-semibold">Date</small>
                                <p class="mb-0 fw-bold text-success">
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
                                <p class="mb-0 fw-semibold">
                                    @php
                                        $start = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                                        $end = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
                                    @endphp
                                    {{ $start }} – {{ $end }}
                                </p>
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

            <!-- Footer with Download Button -->
            <div class="modal-footer border-0 bg-light px-5 py-4 justify-content-between">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>

                <a href="{{ route('student.permit.view', $permit->hashed_id) }}"
                   class="btn btn-success waves-effect waves-light"
                   download="Permit-{{ $permit->hashed_id }}.pdf">
                   Download PDF
                </a>
            </div>
        </div>
    </div>
</div>
        @empty
            <div class="col-12 text-center py-6">
                <i class="bx bx-check-circle bx-lg text-success opacity-30"></i>
                <h5 class="mt-4 text-muted">No approved permits yet</h5>
                <p class="text-muted">Your fully approved permits will appear here once signed by VP for SAS.</p>
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
        transform: translateY(-10px);
        box-shadow: 0 25px 50px rgba(0,0,0,0.15) !important;
        transition: all 0.3s ease;
    }
    .rounded-4 { border-radius: 1rem !important; }
    .shadow-xl { box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important; }
    .avatar { width: 42px; height: 42px; display: flex; align-items: center; justify-content: center; }
    .bg-success { background: linear-gradient(135deg, #28c76f 0%, #1e9e5a 100%) !important; }
</style>
@endsection
