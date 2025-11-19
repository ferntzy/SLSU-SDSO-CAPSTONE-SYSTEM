@php
  $container = 'container-xxl';
  // Sample static data for now
  $eventRequests = [
    ['event_name' => 'Campus Clean-Up Drive', 'status' => 'VP_SAS'],
    ['event_name' => 'Sports Fest 2025', 'status' => 'SDSO_Head'],
    ['event_name' => 'Cultural Night', 'status' => 'Approved'],
    ['event_name' => 'Leadership Seminar', 'status' => 'Submitted']
  ];
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Event Request Progress')

@section('content')
  <div class="{{ $container }}">
    <div class="card shadow-sm">
      <div class="card-header border-bottom d-flex align-items-center justify-content-between bg-primary text-white">
        <h5 class="mb-0">📋 Event Request Progress</h5>
        <span class="badge bg-light text-primary fw-semibold">Demo View</span>
      </div>

      <div class="card-body">
        @foreach($eventRequests as $request)
          <div class="mb-4 p-4 border rounded-3 bg-body-secondary shadow-xs">
            <div class="d-flex justify-content-between align-items-center mb-2">
              <h6 class="fw-semibold text-dark mb-0">{{ $request['event_name'] }}</h6>
              <span class="badge bg-label-{{ $request['status'] == 'Approved' ? 'success' : 'info' }}">
                {{ $request['status'] }}
              </span>
            </div>

            @php
              $stages = ['Submitted', 'SDSO_Head', 'VP_SAS', 'SAS_Director', 'Approved'];
              $currentIndex = array_search($request['status'], $stages);
            @endphp

            <div class="position-relative mt-3">
              <div class="d-flex justify-content-between position-relative">
                @foreach($stages as $index => $stage)
                  <div class="text-center flex-fill position-relative">
                    {{-- Connector line --}}
                    @if(!$loop->first)
                      <div class="position-absolute top-50 start-0 translate-middle-y w-100" style="height: 4px;
                              background-color: {{ $index <= $currentIndex ? '#28c76f' : '#d8dee4' }};
                              z-index: 0;">
                      </div>
                    @endif

                    {{-- Step circle --}}
                    <div class="step-circle mx-auto mb-2 d-flex justify-content-center align-items-center
                          {{ $index <= $currentIndex ? 'step-active' : 'step-inactive' }}">
                      {{ $index + 1 }}
                    </div>

                    {{-- Label --}}
                    <small class="{{ $index <= $currentIndex ? 'text-success fw-semibold' : 'text-muted' }}">
                      {{ str_replace('_', ' ', $stage) }}
                    </small>
                  </div>
                @endforeach
              </div>
            </div>

            <div class="mt-3 text-end">
              <span class="badge rounded-pill
                  {{ $request['status'] == 'Approved' ? 'bg-success' : 'bg-info' }}">
                Current: {{ $request['status'] }}
              </span>
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </div>

  {{-- Custom Styles --}}
  <style>
    .step-circle {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      font-weight: 600;
      z-index: 1;
      position: relative;
      transition: all 0.3s ease;
      font-size: 0.95rem;
    }

    .step-active {
      background-color: #28c76f;
      color: #fff;
      box-shadow: 0 0 10px rgba(40, 199, 111, 0.4);
    }

    .step-inactive {
      background-color: #d8dee4;
      color: #6c757d;
    }

    .step-circle:hover {
      transform: scale(1.1);
    }
  </style>
@endsection