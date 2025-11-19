@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Student Dashboard')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
@endsection

@section('content')
  <div class="{{ $container }} py-4">

    {{-- Welcome Message --}}
    <div class="mb-4">
      <h4 class="fw-bold">Welcome, {{ Auth::user()->username ?? 'Student' }}! 👋</h4>
      <p class="text-muted mb-0">Here’s an overview of your organization’s activities and upcoming events.</p>
    </div>

    {{-- Stats Overview --}}
    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 bg-primary text-white rounded-circle p-3 me-3">
              <i class="bx bx-time-five bx-md"></i>
            </div>
            <div>
              <h6 class="mb-1 text-muted">Pending Events</h6>
              <h4 class="mb-0 fw-bold">{{ $pendingEvents ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 bg-success text-white rounded-circle p-3 me-3">
              <i class="bx bx-check-circle bx-md"></i>
            </div>
            <div>
              <h6 class="mb-1 text-muted">Approved Events</h6>
              <h4 class="mb-0 fw-bold">{{ $approvedEvents ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>

      <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
          <div class="card-body d-flex align-items-center">
            <div class="flex-shrink-0 bg-danger text-white rounded-circle p-3 me-3">
              <i class="bx bx-x-circle bx-md"></i>
            </div>
            <div>
              <h6 class="mb-1 text-muted">Rejected Events</h6>
              <h4 class="mb-0 fw-bold">{{ $rejectedEvents ?? 0 }}</h4>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card mb-4 shadow-sm p-3">
      <h5 class="fw-bold">Organization Information</h5>
      <p><strong>Name:</strong> {{ $organization->organization_name ?? 'N/A' }}</p>
      <p><strong>Type:</strong> {{ $organization->organization_type ?? 'N/A' }}</p>
      <p><strong>Adviser:</strong> {{ $organization->adviser_name ?? 'N/A' }}</p>
      <p><strong>Email:</strong> {{ $organization->contact_email ?? 'N/A' }}</p>
      <p><strong>Contact:</strong> {{ $organization->contact_number ?? 'N/A' }}</p>
    </div>

    {{-- Upcoming Events (Example placeholder) --}}
    <div class="card border-0 shadow-sm">
      <div class="card-header bg-light d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Upcoming Events</h5>
      </div>
      <div class="card-body">
        @if(isset($upcomingEvents) && count($upcomingEvents) > 0)
          <ul class="list-group list-group-flush">
            @foreach($upcomingEvents as $event)
              <li class="list-group-item d-flex justify-content-between align-items-center">
                <span>
                  <i class="bx bx-calendar me-2"></i>
                  <strong>{{ $event->event_title }}</strong> <br>
                  <small class="text-muted">{{ \Carbon\Carbon::parse($event->event_date)->format('M d, Y') }}</small>
                </span>
                <span
                  class="badge bg-{{ $event->proposal_status == 'approved' ? 'success' : ($event->proposal_status == 'pending' ? 'warning' : 'danger') }}">
                  {{ ucfirst($event->proposal_status) }}
                </span>
              </li>
            @endforeach
          </ul>
        @else
          <div class="text-center text-muted py-3">
            <i class="bx bx-calendar-x bx-lg mb-2"></i>
            <p class="mb-0">No upcoming events yet.</p>
          </div>
        @endif
      </div>
    </div>
  </div>
@endsection