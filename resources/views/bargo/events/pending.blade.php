@extends('layouts/contentNavbarLayout')

@section('title', 'BARGO - Pending Events')

@section('content')
  <div class="container py-4">

    <h3 class="mb-4">Pending Event Approvals (BARGO)</h3>

    @if ($pendingReviews->isEmpty())
      <div class="alert alert-info">No pending approvals at the moment.</div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Organization</th>
              <th>Event Title</th>
              <th>Date Submitted</th>
              <th>Status</th>
              <th style="width: 150px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($pendingReviews as $review)
              <tr>
                <td>{{ $review->permit->organization->organization_name ?? 'N/A' }}</td>
                <td>{{ $review->permit->event_title }}</td>
                <td>{{ $review->created_at->format('F d, Y') }}</td>
                <td>
                  <span class="badge bg-warning text-dark">Pending</span>
                </td>
                <td>
                  <a href="{{ route('bargo.view.pdf', $review->permit->hashed_id) }}" target="_blank"
                    class="btn btn-sm btn-primary">
                    View Permit
                  </a>

                  <form action="{{ route('bargo.approve', $review->approval_id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-success">
                      Approve
                    </button>
                  </form>

                  <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                    data-bs-target="#rejectModal{{ $review->approval_id }}">
                    Reject
                  </button>
                </td>
              </tr>

              <!-- Reject Modal -->
              <div class="modal fade" id="rejectModal{{ $review->approval_id }}" tabindex="-1">
                <div class="modal-dialog modal-dialog-centered">
                  <form method="POST" action="{{ route('bargo.reject', $review->approval_id) }}">
                    @csrf
                    <div class="modal-content">
                      <div class="modal-header">
                        <h5 class="modal-title">Reject Permit</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                      </div>
                      <div class="modal-body">
                        <label class="form-label">Reason for rejection</label>
                        <textarea name="comments" class="form-control" rows="4" required></textarea>
                      </div>
                      <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Reject</button>
                      </div>
                    </div>
                  </form>
                </div>
              </div>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </div>
@endsection