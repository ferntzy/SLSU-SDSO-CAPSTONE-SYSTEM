@php $container = 'container-xxl'; @endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'VP SAS Dashboard')

@section('content')
  <div class="{{ $container }} py-4">
    <h4 class="fw-bold mb-4">Vice President for Student & Auxiliary Services Dashboard</h4>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="text-primary">Final Approvals</h5>
            <h2>{{ $finalApprovals ?? 0 }}</h2>
            <p class="text-muted">Total finalized events</p>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm border-0">
          <div class="card-body text-center">
            <h5 class="text-success">Approved Today</h5>
            <h2>{{ $todayApproved ?? 0 }}</h2>
            <p class="text-muted">Events approved in the last 24 hours</p>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection