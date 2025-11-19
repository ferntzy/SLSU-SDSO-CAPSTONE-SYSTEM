@extends('layouts/commonMaster')

@php
    $contentNavbar = true;
    $containerNav = $containerNav ?? 'container-xxl';
    $isNavbar = $isNavbar ?? true;
    $isMenu = $isMenu ?? true;
    $isFlex = $isFlex ?? false;
    $isFooter = $isFooter ?? true;
    $navbarDetached = 'navbar-detached';
    $container = $container ?? 'container-xxl';
@endphp

@section('title', 'Admin Dashboard')

@section('layoutContent')
<div class="layout-wrapper layout-content-navbar {{ $isMenu ? '' : 'layout-without-menu' }}">
  <div class="layout-container">

    @if ($isMenu)
      @include('layouts/sections/menu/verticalMenu')
    @endif

    <!-- Layout page -->
    <div class="layout-page">

      @if ($isNavbar)
        @include('layouts/sections/navbar/navbar')
      @endif

      <!-- Content wrapper -->
      <div class="content-wrapper">
        <div class="{{ $container }} flex-grow-1 container-p-y">

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
          <div class="d-flex justify-content-between align-items-center mb-4 mt-5">
            <!-- Left Side: Greeting and Date -->
            <div class="col-sm text-start">
              <h6 class="mb-1">
                {{ $msg }} <span class="text-muted"> | Current time: {{ $timeNow }}</span>
              </h6>
              <h3 class="page-title mt-2">
                {{ $greet }} {{ Auth::user()->account_role }}!
              </h3>
            </div>

            <!-- Right Side: Dashboard Title -->
            <div class="col-sm text-end">
              <h3 class="fw-bold mb-0">SLSU Event Management Dashboard</h3>
              <p class="text-muted mb-0">Monitor events, users, and organizations at a glance.</p>
            </div>
          </div>

          <!-- Metrics Row -->
          <div class="row g-4">
            <div class="col-md-3 col-sm-6">
              <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                  <i class="ti ti-clock text-warning mb-2" style="font-size:2.5rem;"></i>
                  <h4 id="pendingEvents" class="fw-bold mb-1">—</h4>
                  <p class="text-muted mb-0">Pending Event Requests</p>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                  <i class="ti ti-bolt text-info mb-2" style="font-size:2.5rem;"></i>
                  <h4 id="ongoingEvents" class="fw-bold mb-1">—</h4>
                  <p class="text-muted mb-0">Ongoing / Approved Events</p>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                  <i class="ti ti-check text-success mb-2" style="font-size:2.5rem;"></i>
                  <h4 id="completedEvents" class="fw-bold mb-1">—</h4>
                  <p class="text-muted mb-0">Completed Events</p>
                </div>
              </div>
            </div>

            <div class="col-md-3 col-sm-6">
              <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body text-center">
                  <i class="ti ti-building text-primary mb-2" style="font-size:2.5rem;"></i>
                  <h4 id="organizationsCount" class="fw-bold mb-1">—</h4>
                  <p class="text-muted mb-0">Registered Organizations</p>
                </div>
              </div>
            </div>
          </div>

            <!-- Second Row -->
            <div class="row g-4 mt-1">
              <div class="col-md-4">
                <div class="card border-0 shadow-sm h-100 hover-shadow">
                  {{-- <div class="card-body text-center">
                    <i class="ti ti-users text-secondary mb-2" style="font-size:2.5rem;"></i>
                    <h4 id="activeUsers" class="fw-bold mb-1">—</h4>
                    <p class="text-muted mb-0">Active Users</p>
                  </div> --}}
                </div>
              </div>

            <div class="col-md-8">
              <div class="card border-0 shadow-sm h-100 hover-shadow">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <ul class="list-group list-group-flush" id="upcomingEventsList">
                      <li class="list-group-item text-muted">Loading upcoming events...</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="content-backdrop fade"></div>
        </div>
      </div>

      @if ($isMenu)
        <div class="layout-overlay layout-menu-toggle"></div>
      @endif

      <div class="drag-target"></div>
    </div>
  </div>
</div>
@endsection

@section('page-script')
<script>
  document.addEventListener('DOMContentLoaded', function () {
    const dashboardData = {
      pending: 12,
      ongoing: 5,
      completed: 8,
      organizations: 14,
      activeUsers: 120,
      upcomingEvents: [
        { title: 'Intramurals 2025', date: 'Nov 2, 2025', venue: 'SLSU Field' },
        { title: 'Leadership Seminar', date: 'Nov 5, 2025', venue: 'Auditorium' },
        { title: 'Cultural Fest', date: 'Nov 10, 2025', venue: 'Gymnasium' }
      ]
    };

    document.getElementById('pendingEvents').textContent = dashboardData.pending;
    document.getElementById('ongoingEvents').textContent = dashboardData.ongoing;
    document.getElementById('completedEvents').textContent = dashboardData.completed;
    document.getElementById('organizationsCount').textContent = dashboardData.organizations;

    const eventList = document.getElementById('upcomingEventsList');
    eventList.innerHTML = '';

        if (dashboardData.upcomingEvents.length > 0) {
          dashboardData.upcomingEvents.forEach(event => {
            const li = document.createElement('li');
            li.classList.add('list-group-item', 'd-flex', 'justify-content-between', 'align-items-center');
            li.innerHTML = `
                          <div>
                            <strong>${event.title}</strong><br>
                            <small class="text-muted">${event.date} • ${event.venue}</small>
                          </div>
                          <i class="ti ti-calendar text-primary"></i>
                        `;
            eventList.appendChild(li);
          });
        } else {
          eventList.innerHTML = '<li class="list-group-item text-muted">No upcoming events available.</li>';
        }
      });
    </script>
  @endsection
