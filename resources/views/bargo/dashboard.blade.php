@extends('layouts/contentNavbarLayout')

@section('content')
<div class="container py-4">



  @php
      date_default_timezone_set('Asia/Manila');

      $hour = date('G');
      $msg = 'Today is ' . date('l, M. d, Y.');
      $timeNow = date('h:i:s A');

      if ($hour >= 0 && $hour <= 9) {
          $greet = 'Good Morning';
      } elseif ($hour >= 10 && $hour <= 11) {
          $greet = 'Good Day';
      } elseif ($hour >= 12 && $hour <= 18) {
          $greet = 'Good Afternoon';
      } elseif ($hour >= 18 && $hour <= 23) {
          $greet = 'Good Evening';
      } else {
          $greet = 'Welcome';
      }
  @endphp

  <!-- Header -->
  <div class="d-flex justify-content-between align-items-center mb-4 mt-4">

    <!-- Left Side (Greeting and Time) -->
    <div class="text-start">
      <h6 class="mb-1">
        {{ $msg }}
        <span class="text-muted"> | Current time: {{ $timeNow }}</span>
      </h6>
       <h3 class="page-title fw-semibold mt-2">
                {{ $greet }}  {{ strtoupper(str_replace('_', ' ', Auth::user()->account_role)) }}!
       </h3>
    </div>

    <!-- Right Side (Dashboard Title) -->
    <div class="text-end">
      <h3 class="fw-bold mb-0">Student Event MIS Dashboard</h3>
      <p class="text-muted mb-0">Monitor events, users, and organizations at a glance.</p>
    </div>

  </div>

  <!-- Dashboard Cards -->
  <div class="row">

    <div class="col-md-4">
      <div class="card shadow-sm border-left-primary p-3">
        <h5>Pending Approvals</h5>
        <h2>{{ $pendingReviews }}</h2>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-left-success p-3">
        <h5>Approved</h5>
        <h2>{{ $approved }}</h2>
      </div>
    </div>

    <div class="col-md-4">
      <div class="card shadow-sm border-left-danger p-3">
        <h5>Rejected</h5>
        <h2>{{ $rejected }}</h2>
      </div>
    </div>

  </div>

  <!-- CTA Button -->
  <div class="text-center mt-4">
    <a href="{{ route('bargo.approvals') }}" class="btn btn-primary">
      Review Pending Permits
    </a>
  </div>

</div>
@endsection
