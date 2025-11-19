@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Permit Approval Tracker')

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card shadow-sm border-0">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Permit Approval Tracker</h5>
        <a href="{{ route('student.dashboard') }}" class="btn btn-light btn-sm">
          <i class="bx bx-arrow-back"></i> Back to Dashboard
        </a>
      </div>

      <div class="card-body">
        @if ($permit)
          <div class="mb-4">
            <h6 class="fw-bold">Activity Title:</h6>
            <p class="text-muted">{{ $permit->title_activity }}</p>
          </div>

          <h6 class="fw-bold mb-3">Approval Progress</h6>

          <div class="progress-tracker">
            @foreach ($approvals as $approval)
              <div class="step {{ $approval->status }}">
                <div class="circle">
                  @if ($approval->status === 'approved')
                    <i class="bx bx-check"></i>
                  @elseif ($approval->status === 'rejected')
                    <i class="bx bx-x"></i>
                  @else
                    <i class="bx bx-time"></i>
                  @endif
                </div>
                <div class="label">
                  <strong>{{ $approval->approver_role }}</strong>
                  <br>
                  <small class="text-muted">
                    {{ ucfirst($approval->status) }}
                    @if ($approval->updated_at)
                      <br><span class="text-secondary">{{ $approval->updated_at->format('M d, Y h:i A') }}</span>
                    @endif
                  </small>
                </div>
              </div>
            @endforeach
          </div>

          <div class="mt-5 text-center">
            @if (count($approvals) > 0 && $approvals->every(fn($a) => $a->status === 'approved'))
              <div class="alert alert-success">🎉 Your permit has been fully approved!</div>
            @elseif ($approvals->contains(fn($a) => $a->status === 'rejected'))
              <div class="alert alert-danger">❌ Your permit has been rejected by one or more offices.</div>
            @else
              <div class="alert alert-info">⏳ Your permit is still under review.</div>
            @endif
          </div>
        @else
          <div class="alert alert-info text-center">No permit record found.</div>
        @endif
      </div>
    </div>
  </div>

  <style>
    .progress-tracker {
      display: flex;
      justify-content: space-between;
      align-items: flex-start;
      flex-wrap: wrap;
      position: relative;
      margin-top: 30px;
      padding: 10px 0;
    }

    .progress-tracker::before {
      content: '';
      position: absolute;
      top: 28px;
      left: 6%;
      width: 88%;
      height: 4px;
      background-color: #dcdcdc;
      z-index: 0;
    }

    .step {
      position: relative;
      z-index: 1;
      text-align: center;
      flex: 1;
      min-width: 130px;
    }

    .circle {
      width: 50px;
      height: 50px;
      border-radius: 50%;
      background-color: #e9ecef;
      display: flex;
      justify-content: center;
      align-items: center;
      margin: 0 auto 10px;
      font-size: 24px;
      color: #6c757d;
      transition: all 0.3s ease-in-out;
    }

    .step.approved .circle {
      background-color: #28a745;
      color: white;
      box-shadow: 0 0 10px rgba(40, 167, 69, 0.5);
    }

    .step.rejected .circle {
      background-color: #dc3545;
      color: white;
      box-shadow: 0 0 10px rgba(220, 53, 69, 0.5);
    }

    .step.pending .circle {
      background-color: #ffc107;
      color: white;
      box-shadow: 0 0 10px rgba(255, 193, 7, 0.5);
    }

    .label {
      font-size: 14px;
      line-height: 1.3;
    }

    .label strong {
      display: block;
      font-weight: 600;
    }
  </style>
@endsection