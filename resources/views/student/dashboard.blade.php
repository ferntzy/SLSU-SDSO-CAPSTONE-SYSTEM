@php
    $container = 'container-xxl';

    $approvedPermits = $permits->where('status', 'approved');
    $pendingPermits  = $permits->where('status', 'pending');
    $rejectedPermits = $permits->where('status', 'rejected');

    $ongoingEvents = $permits->where('event_status', 'ongoing');
    $successfulEvents = $permits->where('event_status', 'successful');
    $canceledEvents = $permits->where('event_status', 'canceled');

    $fullName =
        Auth::user()->profile?->first_name . ' ' .
        (Auth::user()->profile?->middle_name ? strtoupper(substr(Auth::user()->profile->middle_name, 0, 1)) . '. ' : '') .
        Auth::user()->profile?->last_name . ' ' .
        Auth::user()->profile?->suffix;
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Permit Tracking')

@section('page-style')
<style>
    .stats-card-modern {
        transition: all 0.3s ease;
    }
    .stats-card-modern:hover {
        transform: translateY(-5px);
        box-shadow: 0 0.5rem 1.5rem rgba(0,0,0,0.1) !important;
    }
    .nav-tabs .nav-link {
        border: none;
        color: #697a8d;
        font-weight: 500;
        padding: 0.75rem 1.25rem;
    }
    .nav-tabs .nav-link.active {
        color: #696cff;
        border-bottom: 2px solid #696cff;
        background: transparent;
    }
    .nav-tabs .nav-link:hover {
        color: #696cff;
        border: none;
    }
    .section-label {
        font-size: 0.8125rem;
        font-weight: 600;
        color: #b4bdc6;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 0.75rem 1.25rem;
        pointer-events: none;
    }
</style>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h4 class="fw-bold">Welcome, {{ trim($fullName) }}!</h4>
      <p class="text-muted mb-0">Monitor your permit submissions and event status</p>
        </div>
    </div>

    {{-- Statistics Cards --}}
    <div class="row g-4 mb-4">

        {{-- Pending --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Pending</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $pendingPermits->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-warning">
                                <i class="bx bx-time-five bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Approved --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Approved</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $approvedPermits->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-check-circle bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Rejected --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Rejected</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $rejectedPermits->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-danger">
                                <i class="bx bx-x-circle bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Ongoing --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Ongoing</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $ongoingEvents->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-primary">
                                <i class="bx bx-play-circle bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Successful --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Successful</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $successfulEvents->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-success">
                                <i class="bx bx-trophy bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Canceled --}}
        <div class="col-sm-6 col-lg-4 col-xl-2">
            <div class="card stats-card-modern">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between">
                        <div class="content-left">
                            <span class="text-muted d-block mb-1">Canceled</span>
                            <div class="d-flex align-items-center">
                                <h3 class="mb-0 me-2">{{ $canceledEvents->count() }}</h3>
                            </div>
                        </div>
                        <div class="avatar">
                            <span class="avatar-initial rounded bg-label-secondary">
                                <i class="bx bx-block bx-sm"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Main Content Card --}}
    <div class="card">
        <div class="card-body">

            {{-- Navigation Tabs --}}
            <ul class="nav nav-tabs" role="tablist">

                {{-- Permits Section --}}
                <li class="section-label">Permits</li>

                <li class="nav-item">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pendingTab" role="tab">
                        <i class="bx bx-time-five me-1"></i>
                        <span class="d-none d-sm-inline">Pending</span>
                        <span class="badge rounded-pill badge-center bg-label-warning ms-1">{{ $pendingPermits->count() }}</span>
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approvedTab" role="tab">
                        <i class="bx bx-check-circle me-1"></i>
                        <span class="d-none d-sm-inline">Approved</span>
                        <span class="badge rounded-pill badge-center bg-label-success ms-1">{{ $approvedPermits->count() }}</span>
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejectedTab" role="tab">
                        <i class="bx bx-x-circle me-1"></i>
                        <span class="d-none d-sm-inline">Rejected</span>
                        <span class="badge rounded-pill badge-center bg-label-danger ms-1">{{ $rejectedPermits->count() }}</span>
                    </button>
                </li>

                {{-- Events Section --}}
                <li class="section-label ms-3">Events</li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#ongoingTab" role="tab">
                        <i class="bx bx-play-circle me-1"></i>
                        <span class="d-none d-sm-inline">Ongoing</span>
                        <span class="badge rounded-pill badge-center bg-label-primary ms-1">{{ $ongoingEvents->count() }}</span>
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#successfulTab" role="tab">
                        <i class="bx bx-trophy me-1"></i>
                        <span class="d-none d-sm-inline">Successful</span>
                        <span class="badge rounded-pill badge-center bg-label-success ms-1">{{ $successfulEvents->count() }}</span>
                    </button>
                </li>

                <li class="nav-item">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#canceledTab" role="tab">
                        <i class="bx bx-block me-1"></i>
                        <span class="d-none d-sm-inline">Canceled</span>
                        <span class="badge rounded-pill badge-center bg-label-secondary ms-1">{{ $canceledEvents->count() }}</span>
                    </button>
                </li>

            </ul>

            {{-- Tab Content --}}
            <div class="tab-content pt-4">

                {{-- PENDING PERMITS --}}
                <div class="tab-pane fade show active" id="pendingTab" role="tabpanel">
                    @include('student.permit.pending', [
                        'items' => $pendingPermits
                    ])
                </div>

                {{-- APPROVED PERMITS --}}
                <div class="tab-pane fade" id="approvedTab" role="tabpanel">
                    @include('student.permit.approved', [
                        'items' => $approvedPermits
                    ])
                </div>

                {{-- REJECTED PERMITS --}}
                <div class="tab-pane fade" id="rejectedTab" role="tabpanel">
                    @include('student.permit.rejected', [
                        'items' => $rejectedPermits
                    ])
                </div>

                {{-- ONGOING EVENTS --}}
                <div class="tab-pane fade" id="ongoingTab" role="tabpanel">
                    @include('student.permit.ongoing', [
                        'items' => $ongoingEvents
                    ])
                </div>

                {{-- SUCCESSFUL EVENTS --}}
                <div class="tab-pane fade" id="successfulTab" role="tabpanel">
                    @include('student.permit.successful', [
                        'items' => $successfulEvents
                    ])
                </div>

                {{-- CANCELED EVENTS --}}
                <div class="tab-pane fade" id="canceledTab" role="tabpanel">
                    @include('student.permit.canceled', [
                        'items' => $canceledEvents
                    ])
                </div>

            </div>

        </div>
    </div>

</div>
@endsection
