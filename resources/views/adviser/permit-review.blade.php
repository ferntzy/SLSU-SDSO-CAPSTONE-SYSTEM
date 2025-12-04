{{-- resources/views/adviser/permit-review.blade.php --}}
@extends('layouts.adviserLayout')

@section('title', 'Review Permit Request')

@section('page-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@mdi/font@7.4.47/css/materialdesignicons.min.css">
<style>
    .permit-header { background: linear-gradient(135deg, #696cff, #5a5cdb); color: white; padding: 2.5rem 2rem; border-radius: 1.25rem 1.25rem 0 0; }
    .permit-body { padding: 2.5rem 2rem; background: white; border-radius: 0 0 1.25rem 1.25rem; box-shadow: 0 10px 40px rgba(0,0,0,0.08); }
    .detail-label { font-weight: 600; color: #566a7f; }
    .detail-value { font-size: 1.1rem; color: #444; }
    .detail-section { margin-bottom: 2.5rem; }
    .action-card { background: #f8f9ff; border-radius: 1rem; padding: 1.75rem; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
    .file-item {
        display: flex; align-items: center; gap: 1rem; padding: 1rem 1.25rem;
        background: #f8f9ff; border-radius: 0.75rem; margin-bottom: 0.75rem;
        transition: all 0.3s ease;
    }
    .file-item:hover { background: #eef0ff; transform: translateY(-2px); box-shadow: 0 5px 15px rgba(105,108,255,0.15); }
    .file-item a { color: #696cff; font-weight: 600; text-decoration: none; }
    .file-item a:hover { color: #5a5cdb; }
    .no-signature-box {
        background: #fff3cd; border: 1px solid #ffeaa7; color: #856404;
        padding: 1.5rem; border-radius: 1rem; text-align: center; font-size: 1.1rem;
    }
</style>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row">
        <div class="col-12">
            <!-- Main Permit Card -->
            <div class="permit-header text-center">
                <h2 class="fs-3 fw-bold mb-2">{{ $permit->title_activity }}</h2>
                <p class="mb-0 opacity-90">Permit Request Review</p>
            </div>

            <div class="permit-body">
                <div class="row g-5 detail-section">

                    <!-- Basic Info -->
                    <div class="col-lg-8">
                        <h5 class="fw-bold text-primary mb-4">Activity Details</h5>
                        <div class="row g-4">
                            <div class="col-md-6">
                                <p class="detail-label mb-1">Organization</p>
                                <span class="badge bg-primary fs-6 px-4 py-3">
                                    {{ $permit->organization->organization_name }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Nature of Activity</p>
                                <span class="badge {{ $isOffCampus ? 'bg-danger' : 'bg-success' }} fs-6 px-4 py-3">
                                    {{ ucfirst($permit->nature ?? '—') }}
                                </span>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Date</p>
                                <p class="detail-value fs-5 fw-bold mb-0">
                                    {{ \Carbon\Carbon::parse($permit->date_start)->format('F j, Y') }}
                                    @if($permit->date_end && $permit->date_end != $permit->date_start)
                                        → {{ \Carbon\Carbon::parse($permit->date_end)->format('F j, Y') }}
                                    @endif
                                </p>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Time</p>
                                <p class="detail-value fs-5 fw-bold mb-0">
                                    {{ $permit->time_start ? $permit->time_start . ($permit->time_end ? ' – ' . $permit->time_end : '') : 'All Day' }}
                                </p>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Venue</p>
                                <p class="detail-value">{{ $permit->venue ?? '<em class="text-muted">Not specified</em>' }}</p>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Activity Type</p>
                                <span class="badge bg-label-info">{{ $permit->type ?? '—' }}</span>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Expected Participants</p>
                                <p class="detail-value fw-bold">{{ $permit->participants ?? '—' }}</p>
                            </div>

                            <div class="col-md-6">
                                <p class="detail-label mb-1">Number of Participants</p>
                                <p class="detail-value fw-bold">{{ $permit->number ?? '—' }}</p>
                            </div>
                        </div>

                        <!-- Purpose / Description -->
                        <div class="mt-5">
                            <h5 class="fw-bold text-primary mb-3">
                                Purpose of Activity
                            </h5>
                            <div class="bg-light p-4 rounded border" style="line-height: 1.8; min-height: 150px;">
                                {!! nl2br(e($permit->purpose ?? '<em class="text-muted">No description provided</em>')) !!}
                            </div>
                        </div>

                        <!-- Off-Campus Documents -->
                        @if($isOffCampus)
                            <div class="mt-5">
                                <h5 class="fw-bold text-primary mb-4">
                                    Attached Documents
                                </h5>

                                @if($files->isNotEmpty())
                                    <div class="row g-3">
                                        @foreach($files as $file)
                                            <div class="col-12">
                                                <div class="file-item">
                                                    <i class="mdi mdi-file-document-outline mdi-36px text-primary"></i>
                                                    <div class="flex-grow-1">
                                                        <a href="{{ $file['url'] }}" target="_blank">
                                                            {{ $file['name'] }}
                                                        </a>
                                                        <small class="text-muted d-block">Click to download or view</small>
                                                    </div>
                                                    <i class="mdi mdi-download mdi-24px text-muted"></i>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-center py-5 bg-light rounded">
                                        <i class="mdi mdi-file-off mdi-60px text-muted opacity-50"></i>
                                        <p class="mt-3 text-muted">No documents attached</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="alert alert-info mt-4 rounded-1">
                                <i class="mdi mdi-information me-2"></i>
                                This is an <strong>on-campus</strong> activity — no supporting documents required.
                            </div>
                        @endif
                    </div>

                    <!-- Action Panel -->
                    <div class="col-lg-4">
                        <div class="action-card">
                            <h5 class="fw-bold text-center mb-4">
                                Take Action
                            </h5>

                            @if($hasSignature)
                                <div class="d-grid gap-3">
                                    <form action="{{ route('adviser.permit.approve', $permit->hashed_id) }}" method="POST" style="display:inline;">
                                        @csrf
                                        <input type="hidden" name="flow_id" value="{{ $flow->id }}">
                                        <button type="submit" class="btn btn-success btn-lg rounded-pill shadow-sm" onclick="return confirmApprove()">
                                            <i class="mdi mdi-check-bold me-2"></i>Approve Permit
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-danger btn-lg rounded-pill shadow-sm" onclick="showRejectModal()">
                                        <i class="mdi mdi-close-thick me-2"></i>Reject Permit
                                    </button>
                                </div>
                            @else
                                <div class="no-signature-box">
                                    <i class="mdi mdi-alert-circle mdi-48px d-block mx-auto mb-3 text-warning"></i>
                                    <strong>Cannot Take Action</strong><br><br>
                                    Please upload your <strong>digital signature</strong> in your profile first.
                                    <a href="{{ route('adviser.profile') }}" class="btn btn-outline-warning btn-sm mt-3">
                                        Go to Profile
                                    </a>
                                </div>
                            @endif

                            <div class="mt-4 text-center text-muted small">
                                Submitted {{ $permit->created_at->diffForHumans() }}
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-5">
                    <a href="{{ route('adviser.approvals') }}" class="btn btn-outline-secondary px-5 rounded-pill">
                        <i class="mdi mdi-arrow-left me-2"></i> Back to Pending Approvals
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<script>
function showRejectModal() {
    Swal.fire({
        title: 'Reject Permit Request',
        html: `
            <p class="mb-3">Please provide a reason for rejection:</p>
            <textarea id="rejectReason" class="form-control" rows="5" placeholder="Enter reason here..." required></textarea>
        `,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Yes, Reject',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#dc3545',
        preConfirm: () => {
            const reason = document.getElementById('rejectReason').value.trim();
            if (!reason) {
                Swal.showValidationMessage('Reason is required');
            }
            return reason;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{{ route('adviser.permit.reject', $permit->hashed_id) }}';

            const csrf = document.createElement('input');
            csrf.type = 'hidden';
            csrf.name = '_token';
            csrf.value = '{{ csrf_token() }}';
            form.appendChild(csrf);

            const flowInput = document.createElement('input');
            flowInput.type = 'hidden';
            flowInput.name = 'flow_id';
            flowInput.value = '{{ $flow->id }}';
            form.appendChild(flowInput);

            const reasonInput = document.createElement('input');
            reasonInput.type = 'hidden';
            reasonInput.name = 'reason';
            reasonInput.value = result.value;
            form.appendChild(reasonInput);

            document.body.appendChild(form);
            form.submit();
        }
    });
}

function confirmApprove() {
    return confirm('Are you sure you want to approve this permit? This action cannot be undone.');
}
</script>
@endsection
