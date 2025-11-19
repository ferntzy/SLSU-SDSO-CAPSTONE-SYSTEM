@extends('layouts/contentNavbarLayout')

@section('content')
  <div class="container py-4">

    <h2 class="mb-4">Pending Permit Approvals (BARGO)</h2>

    @if($pendingPermits->isEmpty())
      <div class="alert alert-info">No pending permits at this time.</div>
    @else
      <table class="table table-bordered table-striped">
        <thead>
          <tr>
            <th>Organization</th>
            <th>Event Title</th>
            <th>Submitted On</th>
            <th width="230">Action</th>
          </tr>
        </thead>
        <tbody>
          @foreach($pendingPermits as $flow)
            <tr>
              <td>{{ $flow->permit->organization->name }}</td>
              <td>{{ $flow->permit->event_title }}</td>
              <td>{{ $flow->created_at->format('F d, Y') }}</td>
              <td>
                <a href="{{ route('bargo.permit.pdf', $flow->permit->hashed_id) }}" target="_blank"
                  class="btn btn-sm btn-secondary">View PDF</a>

                <form action="{{ route('bargo.approve', $flow->approval_id) }}" method="POST" class="d-inline">
                  @csrf
                  <button class="btn btn-sm btn-success">Approve</button>
                </form>

                <button class="btn btn-sm btn-danger" data-bs-toggle="modal"
                  data-bs-target="#rejectModal{{ $flow->approval_id }}">Reject</button>

                <!-- Modal -->
                <div class="modal fade" id="rejectModal{{ $flow->approval_id }}">
                  <div class="modal-dialog">
                    <form action="{{ route('bargo.reject', $flow->approval_id) }}" method="POST">
                      @csrf
                      <div class="modal-content">
                        <div class="modal-header">
                          <h5 class="modal-title">Reject Permit</h5>
                        </div>
                        <div class="modal-body">
                          <textarea name="comments" class="form-control" placeholder="Reason for rejection"
                            required></textarea>
                        </div>
                        <div class="modal-footer">
                          <button type="submit" class="btn btn-danger">Submit</button>
                          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>

              </td>
            </tr>
          @endforeach
        </tbody>
      </table>
    @endif

  </div>
@endsection