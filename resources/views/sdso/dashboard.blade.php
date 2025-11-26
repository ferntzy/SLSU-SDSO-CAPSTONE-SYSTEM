@php
    $container = 'container-xxl';

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

@extends('layouts/contentNavbarLayout')
@section('title', 'SDSO Dashboard')

@section('content')
<div class="{{ $container }} py-4">

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

  <!-- Stats Cards -->
  <div class="row g-4">

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-1">Pending Endorsements</h5>
          <h2 class="text-primary">{{ $pending ?? 0 }}</h2>
        </div>
      </div>
    </div>

    <div class="col-md-6">
      <div class="card shadow-sm">
        <div class="card-body">
          <h5 class="mb-1">Approved Events</h5>
          <h2 class="text-success">{{ $approved ?? 0 }}</h2>
        </div>
      </div>
    </div>

  </div>

</div>
@endsection
