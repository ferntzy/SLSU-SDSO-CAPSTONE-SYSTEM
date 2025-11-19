@extends('layouts/contentNavbarLayout')

@section('content')
  <div class="container py-4">
    <h2 class="mb-4">BARGO Dashboard</h2>

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

    <div class="text-center mt-4">
      <a href="{{ route('bargo.approvals') }}" class="btn btn-primary">Review Pending Permits</a>
    </div>

  </div>
@endsection