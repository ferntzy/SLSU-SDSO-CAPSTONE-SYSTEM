{{-- resources/views/bargo/events/pending.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Pending Review - BARGO')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-warning text-white" style="background: linear-gradient(135deg, #ffd43b 0%, #ffa726 100%);">
                <div class="card-body">
                    <h4 class="text-white mb-0">
                        <i class="mdi mdi-clock-alert-outline me-2"></i>
                        Pending Permits for Your Review
                    </h4>
                    <p class="mb-0 opacity-75">You are the second-level approver (after Faculty Adviser)</p>
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
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingReviews as $flow)
                                @php $permit = $flow->permit; @endphp
                                <tr>
                                    <td>
                                        <div class="fw-medium">{{ $permit->organization->name  ?? 'N/A' }}</div>

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
                                    <td>
                                        <a href="{{ route('bargo.permit.pdf', $permit->hashed_id) }}" target="_blank"
                                           class="btn btn-sm btn-outline-primary me-1" title="View PDF">
                                            <i class="mdi mdi-eye"></i>
                                        </a>
                                        <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal"
                                                data-bs-target="#approveModal{{ $flow->approval_id }}">
                                            <i class="mdi mdi-check"></i> Approve
                                        </button>
                                        <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                                                data-bs-target="#rejectModal{{ $flow->approval_id }}">
                                            <i class="mdi mdi-close"></i> Reject
                                        </button>
                                    </td>
                                </tr>

                                {{-- Approve Modal --}}
                                <div class="modal fade" id="approveModal{{ $flow->approval_id }}">
                                    <div class="modal-dialog modal-lg">
                                        <form action="{{ route('bargo.approve', $flow->approval_id) }}" method="POST" enctype="multipart/form-data">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header bg-success text-white">
                                                    <h5 class="modal-title">Approve & Sign Permit</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Permit: <strong>{{ $permit->title_activity }}</strong></p>
                                                    <div class="row g-3">
                                                        <div class="col-md-6">
                                                            <label>Draw Signature</label>
                                                            <canvas id="canvas{{ $flow->approval_id }}" class="border w-100" height="150"></canvas>
                                                            <input type="hidden" name="signature_data" id="sigData{{ $flow->approval_id }}">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="clearCanvas('{{ $flow->approval_id }}')">
                                                                Clear
                                                            </button>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label>Or Upload Signature</label>
                                                            <input type="file" name="signature_upload" accept="image/*" class="form-control">
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                    <button type="submit" class="btn btn-success">Approve & Sign</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                                {{-- Reject Modal --}}
                                <div class="modal fade" id="rejectModal{{ $flow->approval_id }}">
                                    <div class="modal-dialog">
                                        <form action="{{ route('bargo.reject', $flow->approval_id) }}" method="POST">
                                            @csrf
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Permit</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Permit: <strong>{{ $permit->title_activity }}</strong></p>
                                                    <div class="mb-3">
                                                        <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
                                                        <textarea name="comments" class="form-control" rows="4" required placeholder="Please explain why this permit is being rejected..."></textarea>
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
                <div class="text-center py-5">
                    <i class="mdi mdi-check-all text-success" style="font-size: 4rem;"></i>
                    <h5 class="mt-3">No Pending Permits</h5>
                    <p class="text-muted">All permits are up to date!</p>
                </div>
            @endif
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script>
    document.querySelectorAll('canvas').forEach(canvas => {
        const id = canvas.id.replace('canvas', '');
        const signaturePad = new SignaturePad(canvas);
        document.getElementById('sigData' + id).value = '';

        canvas.addEventListener('mouseup', () => {
            document.getElementById('sigData' + id).value = signaturePad.toDataURL();
        });
    });

    function clearCanvas(id) {
        const canvas = document.getElementById('canvas' + id);
        const signaturePad = new SignaturePad(canvas);
        signaturePad.clear();
        document.getElementById('sigData' + id).value = '';
    }
</script>
@endsection
