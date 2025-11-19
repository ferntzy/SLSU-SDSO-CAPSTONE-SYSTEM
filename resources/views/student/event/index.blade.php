@php $container = 'container-xxl'; @endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'My Events')

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">My Event Requests</h5>
        <a href="{{ route('permit.form') }}" class="btn btn-light btn-sm">+ New Permit</a>
      </div>

      <div class="card-body">
        @if($events->isEmpty())
          <div class="alert alert-info text-center">No event requests yet.</div>
        @else
          <div class="table-responsive">
            <table class="table table-bordered align-middle">
              <thead class="table-primary text-center">
                <tr>
                  <th>Title</th>
                  <th>Date</th>
                  <th>Status</th>
                  <th>Created</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($events as $event)
                  <tr>
                    <td>{{ $event->event_title }}</td>
                    <td>{{ $event->event_date ? \Carbon\Carbon::parse($event->event_date)->format('M d, Y') : 'TBD' }}</td>
                    <td>
                      <span class="badge
                                @if($event->proposal_status == 'approved') bg-success
                                @elseif($event->proposal_status == 'rejected') bg-danger
                                @else bg-warning text-dark
                                @endif">
                        {{ ucfirst($event->proposal_status) }}
                      </span>
                    </td>
                    <td>{{ \Carbon\Carbon::parse($event->created_at)->format('M d, Y h:i A') }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection