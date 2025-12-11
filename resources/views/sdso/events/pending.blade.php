{{-- resources/views/bargo/events/pending.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Pending Review - SDS')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-gradient-warning text-white">
                <div class="card-body">
                    <h4 class="text-white mb-0">
                        Pending Permits for Your Review
                    </h4>
                    <p class="mb-0 opacity-90">You are the second-level approver (after Faculty Adviser)</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($pendingReviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Organization</th>
                                <th>Event Title</th>
                                <th>Type</th>
                                <th>Submitted</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReviews as $flow)
                                @php $permit = $flow->permit; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $permit->organization->organization_name ?? 'N/A' }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-primary">
                                            {{ Str::limit($permit->title_activity, 50) }}
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-label-info">{{ ucfirst($permit->type ?? 'Event') }}</span>
                                    </td>
                                    <td>
                                        <small>{{ $permit->created_at->format('M d, Y h:i A') }}</small>
                                    </td>
                                    <td class="text-center">
                                        <!-- View PDF -->
                                        <a href="{{ route('sdso.permit.pdf', $permit->hashed_id) }}" target="_blank"
                                           class="btn btn-sm btn-outline-primary me-2" title="View PDF">
                                            View PDF
                                        </a>

                                        <!-- Approve Button — Uses Your Saved Signature -->
                                        <button class="btn btn-sm btn-success me-2"
                                                onclick="approvePermit({{ $flow->approval_id }}, '{{ addslashes($permit->title_activity) }}')">
                                            Approve
                                        </button>

                                        <!-- Reject Button -->
                                        <button class="btn btn-sm btn-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $flow->approval_id }}">
                                            Reject
                                        </button>
                                    </td>
                                </tr>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal{{ $flow->approval_id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form action="{{ route('sdso.reject', $flow->approval_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Permit</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>{{ $permit->title_activity }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="comments" class="form-control" rows="4" required
                                                                  placeholder="Explain why this permit is being rejected..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-danger">Reject Permit</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-6">
                    <i class="ti ti-checks text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-4">No Pending Permits</h5>
                    <p class="text-muted">All permits are up to date!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
function approvePermit(approvalId, title) {
    Swal.fire({
        title: 'Approve Permit?',
        text: `"${title}" will be approved using your saved signature.`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, Approve',
        confirmButtonColor: '#28a745',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (!result.isConfirmed) return;

        const btn = event.target;
        btn.disabled = true;
        btn.innerHTML = 'Approving...';

        fetch(`/sdso/approve/${approvalId}`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({})
        })
        .then(response => {
            if (!response.ok) {
                return response.text().then(text => { throw new Error(text); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire('Approved!', 'Permit signed and approved.', 'success')
                    .then(() => location.reload());
            } else {
                throw new Error(data.message || 'Approval failed');
            }
        })
        .catch(err => {
            console.error('Approval Error:', err);
            Swal.fire('Error', 'Failed to approve. Please try again.', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Approve';
        });
    });
}
</script>
@endsection
