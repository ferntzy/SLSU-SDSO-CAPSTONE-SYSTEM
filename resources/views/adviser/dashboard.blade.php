@extends('layouts/contentNavbarLayout')
@section('title', 'Faculty Adviser Dashboard')
@php $container = 'container-xxl'; @endphp

@section('content')
  <div class="{{ $container }} py-4">
    <h4 class="fw-bold mb-4">Dashboard Overview</h4>

    <div class="row g-4 mb-4">
      <div class="col-md-4">
        <div class="card shadow-sm text-center">
          <h5 class="text-primary mt-3">Pending Reviews</h5>
          <h2>{{ $pendingReviews }}</h2>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm text-center">
          <h5 class="text-success mt-3">Approved</h5>
          <h2>{{ $approved }}</h2>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card shadow-sm text-center">
          <h5 class="text-danger mt-3">Rejected</h5>
          <h2>{{ $rejected }}</h2>
        </div>
      </div>
    </div>
  </div>
@endsection