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

    <!-- Clean Table with MDI Icons -->
    <div class="card border shadow-none">
        <div class="card-header bg-danger bg-opacity-10 border-0 d-flex align-items-center justify-content-between">
            <h5 class="mb-0">Rejected Permit Applications</h5>
            <small class="text-muted">{{ $permits->total() }} record{{ $permits->total() !== 1 ? 's' : '' }}</small>
        </div>

        <div class="table-responsive text-nowrap ">
            <table class="table table-hover table-borderless mb-0 ">
                <thead class="table-light">
                    <tr>
                        <th width="25%">Activity Title</th>
                        <th width="18%">Date & Time</th>
                        <th width="15%">Venue</th>
                        <th width="18%">Rejected By</th>
                        <th>Reason / Comment</th>
                        <th width="10%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="table-border-bottom-0">
                    @forelse($permits as $permit)
                        @php
                            $rejectApproval = $permit->approvals->where('status', 'rejected')->first();
                            $rejectedBy     = $rejectApproval?->approver?->profile?->full_name ??
                                             ($rejectApproval?->approver_role ?? 'Unknown');
                            $rejectedReason = $rejectApproval?->comments ?? 'No comment provided.';
                            $rejectedAt     = $rejectApproval?->updated_at?->format('M d, Y') ?? '—';

                            $dateDisplay = $permit->date_start
                                ? \Carbon\Carbon::parse($permit->date_start)->format('M d, Y')
                                : '—';
                            if ($permit->date_end && $permit->date_end !== $permit->date_start) {
                                $dateDisplay .= ' to ' . \Carbon\Carbon::parse($permit->date_end)->format('M d, Y');
                            }

                            $timeDisplay = ($permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?') .
                                          ' - ' .
                                          ($permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?');
                        @endphp

                        <tr class="hover-row">
                            <td>
                                <div>
                                    <strong class="d-block text-dark">{{ Str::limit($permit->title_activity, 50) }}</strong>
                                    <small class="text-muted">
                                        <span class="badge bg-label-danger fs-xxs">Rejected</span>
                                        <span class="mx-1">•</span>
                                        {{ $rejectedAt }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <small>
                                    <i class="mdi mdi-calendar-month mdi-18px text-muted me-1"></i>{{ $dateDisplay }}<br>
                                    <i class="mdi mdi-clock-outline mdi-18px text-muted me-1"></i>{{ $timeDisplay }}
                                </small>
                            </td>
                            <td>
                                <small class="text-muted">{{ Str::limit($permit->venue, 30) }}</small>
                            </td>
                            <td>
                                <small class="fw-medium text-danger">{{ Str::limit($rejectedBy, 25) }}</small>
                            </td>
                            <td>
                                <span class="text-danger small fst-italic" title="{{ $rejectedReason }}">
                                    {{ Str::limit($rejectedReason, 80) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button type="button"
                                        class="btn btn-icon btn-outline-danger btn-sm rounded-pill"
                                        data-bs-toggle="modal"
                                        data-bs-target="#rejectedModal{{ $permit->permit_id }}"
                                        title="View Details">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                            </td>
                        </tr>

                        {{-- Modal - Full Details with MDI Icons --}}
                        <div class="modal fade" id="rejectedModal{{ $permit->permit_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow-lg border-0">
                                    <div class="modal-header border-0 pb-4">
                                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2">
                                            <i class="mdi mdi-file-document text-danger"></i> Permit Details
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>

                                    <div class="modal-body pt-3">
                                        <div class="d-flex justify-content-between align-items-start mb-4">
                                            <div>
                                                <h5 class="text-danger fw-bold">{{ $permit->title_activity }}</h5>
                                                <div class="d-flex gap-2 mt-2">
                                                    <span class="badge bg-label-danger">Rejected</span>
                                                    <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }}">
                                                        {{ $permit->type }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <hr>

                                        <div class="alert bg-light-danger border-danger border-opacity-25 rounded-3 mb-4">
                                            <div class="d-flex align-items-start gap-3">
                                                <i class="mdi mdi-close-circle text-danger fs-3 mt-1"></i>
                                                <div>
                                                    <h6 class="fw-bold text-danger mb-1">Permit Rejected</h6>
                                                    <p class="mb-1"><strong>By:</strong> {{ $rejectedBy }}</p>
                                                    <p class="mb-2"><strong>On:</strong> {{ $rejectedAt }}</p>
                                                    <div class="bg-white border rounded-3 p-3 shadow-sm">
                                                        <small class="text-muted text-uppercase fw-bold d-block mb-2">Rejection Comment</small>
                                                        <p class="mb-0 text-dark fw-medium">{{ $rejectedReason }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row g-4">
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-bullseye-arrow text-muted"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Purpose</small>
                                                        <p class="mb-0">{{ $permit->purpose ?? '—' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-tag-outline text-info"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Nature</small>
                                                        <p class="mb-0">{{ $permit->nature ?? '—' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-map-marker text-danger"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Venue</small>
                                                        <p class="mb-0 fw-semibold">{{ $permit->venue }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-calendar-range text-danger"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Date</small>
                                                        <p class="mb-0 fw-bold text-danger">{{ $dateDisplay }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-clock-outline text-primary"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Time</small>
                                                        <p class="mb-0 fw-semibold">{{ $timeDisplay }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-account-group text-info"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Participants</small>
                                                        <p class="mb-0">{{ $permit->participants ?? '—' }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex gap-3">
                                                    <i class="mdi mdi-account-plus text-danger"></i>
                                                    <div>
                                                        <small class="text-muted text-uppercase fw-semibold">Expected Attendees</small>
                                                        <p class="mb-0 fw-bold">{{ $permit->number ?? '—' }} persons</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="modal-footer border-0 bg-light">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                                            <i class="mdi mdi-close me-1"></i> Close
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-6">
                                <i class="mdi mdi-check-circle-outline mdi-48px text-success opacity-30"></i>
                                <h5 class="mt-4 text-muted">No rejected permits</h5>
                                <p class="text-muted mb-4">All your submitted permits are either pending or approved.</p>
                                {{-- <a href="{{ route('student.permits.create') }}" class="btn btn-primary btn-sm">
                                    <i class="mdi mdi-plus me-1"></i> Submit New Permit
                                </a> --}}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($permits->hasPages())
            <div class="card-footer bg-transparent border-top">
                {{ $permits->onEachSide(1)->links('pagination::bootstrap-5') }}
            </div>
        @endif
    </div>
</div>
@endsection

@section('page-style')
<style>
    .hover-row:hover {
        background-color: rgba(255, 107, 107, 0.04) !important;
    }
    .table > :not(caption) > * > * {
        padding: 0.85rem 1rem;
    }
    .fs-xxs {
        font-size: 0.65rem;
    }
</style>
@endsection
