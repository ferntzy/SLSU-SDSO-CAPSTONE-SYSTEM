@extends('layouts/contentNavbarLayout')

@section('title', 'BARGO - Approved Events')

@section('content')
  <div class="container py-4">

    <h3 class="mb-4">Approved Event Permits (BARGO)</h3>

    @if ($approvedReviews->isEmpty())
      <div class="alert alert-info">No approved permits yet.</div>
    @else
      <div class="table-responsive">
        <table class="table table-hover align-middle">
          <thead class="table-light">
            <tr>
              <th>Organization</th>
              <th>Event Title</th>
              <th>Date Approved</th>
              <th>Status</th>
              <th style="width: 130px;">Action</th>
            </tr>
          </thead>
          <tbody>
            @foreach ($approvedReviews as $review)
              {{-- <tr>
                <td>{{ $review->permit->organization->organization_name ?? 'N/A' }}</td>
                <td>{{ $review->permit->event_title }}</td>
                <td>{{ $review->approved_at ? $review->approved_at->format('F d, Y') : 'N/A' }}</td> --}}
                <td>
                  <span class="badge bg-success">Approved</span>
                </td>
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