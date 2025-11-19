@extends('layouts/contentNavbarLayout')

@section('title', 'BARGO - Approval History')

@section('content')
  <div class="container py-4">

    <h3 class="mb-4">Approval History (BARGO)</h3>

    @if ($historyReviews->isEmpty())
      <div class="alert alert-info">No records found.</div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Organization</th>
              <th>Event Title</th>
              <th>Status</th>
              <th>Remarks</th>
              <th>Date</th>
              <th style="width: 130px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($historyReviews as $review)
              <tr>
                <td>{{ $review->permit->organization->organization_name ?? 'N/A' }}</td>
                <td>{{ $review->permit->event_title }}</td>
                <td>
                  @if($review->status == 'approved')
                    <span class="badge bg-success">Approved</span>
                  @else
                    <span class="badge bg-danger">Rejected</span>
                  @endif
                </td>
                <td>{{ $review->comments ?: 'None' }}</td>
                <td>{{ $review->updated_at->format('F d, Y') }}</td>
                <td>
                  <a href="{{ route('bargo.view.pdf', $review->permit->hashed_id) }}" target="_blank"
                    class="btn btn-sm btn-primary">
                    View PDF
                  </a>
                </td>
              </tr>
            @endforeach
          </tbody>
        </table>
      </div>
    @endif

  </div>
@endsection