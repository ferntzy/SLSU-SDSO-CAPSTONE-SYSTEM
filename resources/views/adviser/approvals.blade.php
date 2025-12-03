{{-- resources/views/adviser/approvals.blade.php --}}
@extends('layouts.adviserLayout')

@section('title', 'Pending Approvals')

@section('page-style')
<style>
    .permit-card {
        transition: all 0.3s ease;
        border-left: 5px solid transparent;
    }
    .permit-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 10px 25px rgba(105, 108, 255, 0.15) !important;
        border-left-color: #696cff;
    }
    .action-btn {
        width: 38px;
        height: 38px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }
    .status-badge {
        min-width: 90px;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">
                        <i class="ti ti-clock text-warning me-2"></i>
                        Pending Approvals
                    </h4>
                    <p class="text-muted mb-0">
                        Review and approve/reject permit requests from your advised organizations
                    </p>
                </div>
                <div>
                    <span class="badge bg-label-warning fs-5 px-3 py-2">
                        {{ $pendingPermits->count() }} Pending
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Permits List -->
    <div class="row g-4">
        @forelse($pendingPermits as $flow)
            @php
                $permit = $flow->permit;
                $org = $permit->organization;
            @endphp

            <div class="col-12">
                <div class="card permit-card h-100 border shadow-sm">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <!-- Left: Permit Info -->
                            <div class="col-lg-8">
                                <div class="d-flex align-items-start gap-3">
                                    <div class="avatar flex-shrink-0">
                                        <span class="avatar-initial rounded-circle bg-label-primary fs-5">
                                            {{ strtoupper(substr($org->organization_name, 0, 2)) }}
                                        </span>
                                    </div>
                                    <div class="w-100">
                                        <h6 class="mb-1 fw-bold text-dark">
                                            {{ $permit->title_activity }}
                                        </h6>
                                        <div class="d-flex flex-wrap gap-3 text-muted small">
                                            <div>
                                                <i class="ti ti-building me-1"></i>
                                                <strong>{{ $org->organization_name }}</strong>
                                            </div>
                                            <div>
                                                <i class="ti ti-calendar-event me-1"></i>
                                                {{ \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') }}
                                                @if($permit->date_end && $permit->date_end != $permit->date_start)
                                                    → {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}
                                                @endif
                                            </div>
                                            <div>
                                                <i class="ti ti-map-pin me-1"></i>
                                                {{ $permit->venue ?? 'Not specified' }}
                                            </div>
                                        </div>
                                        @if($permit->type)
                                            <span class="badge bg-label-info mt-2">
                                                {{ $permit->type }}
                                            </span>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            <!-- Right: Actions -->
                            <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                                <div class="d-flex justify-content-lg-end gap-2 flex-wrap">

                                    <button type="button"
                                            class="btn btn-success action-btn"
                                            onclick="approvePermit('{{ $permit->permit_id }}', '{{ $flow->id }}')"
                                            title="Approve">
                                        <i class="ti ti-check"></i>
                                    </button>

                                    <button type="button"
                                            class="btn btn-danger action-btn"
                                            onclick="rejectPermit('{{ $permit->permit_id }}', '{{ $flow->id }}')"
                                            title="Reject">
                                        <i class="ti ti-x"></i>
                                    </button>
                                </div>

                                <div class="mt-3">
                                    <span class="badge bg-label-warning status-badge">
                                        <i class="ti ti-clock ti-xs me-1"></i>
                                        Awaiting Your Approval
                                    </span>
                                </div>
                                <small class="text-muted d-block mt-2">
                                    Submitted {{ \Carbon\Carbon::parse($permit->created_at)->diffForHumans() }}
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        @empty
            <div class="col-12">
                <div class="card border-dashed text-center py-6">
                    <div class="card-body">
                        <i class="ti ti-checks ti-48px text-success mb-3"></i>
                        <h5 class="mb-2">All Caught Up!</h5>
                        <p class="text-muted mb-0">
                            No pending approvals at the moment.<br>
                            You're doing great!
                        </p>
                    </div>
                </div>
            </div>
        @endforelse
    </div>

</div>
@endsection

@section('page-script')
<script>
function approvePermit(permitId, flowId) {
    Swal.fire({
        title: 'Approve Permit?',
        text: "This action will mark the permit as approved by you.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#28a745',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/adviser/approve/${permitId}`, {
                flow_id: flowId
            }).then(() => {
                location.reload();
            }).catch(() => {
                Swal.fire('Error', 'Something went wrong.', 'error');
            });
        }
    });
}

function rejectPermit(permitId, flowId) {
    Swal.fire({
        title: 'Reject Permit?',
        html: `
            <p>Are you sure you want to reject this permit?</p>
            <div class="mt-3">
                <label class="form-label">Reason for rejection (optional)</label>
                <textarea id="rejectReason" class="form-control" rows="3" placeholder="Enter reason..."></textarea>
            </div>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        reverseButtons: true,
        preConfirm: () => {
            return document.getElementById('rejectReason').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            axios.post(`/adviser/reject/${permitId}`, {
                flow_id: flowId,
                reason: result.value || 'No reason provided'
            }).then(() => {
                location.reload();
            }).catch(() => {
                Swal.fire('Error', 'Something went wrong.', 'error');
            });
        }
    });
}
</script>
@endsection
