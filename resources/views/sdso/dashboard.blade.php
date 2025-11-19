@php $container = 'container-xxl'; @endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'SDSO Dashboard')

@section('content')
  <div class="{{ $container }} py-4">
    <h4 class="fw-bold mb-4">Student Development and Services Office Dashboard</h4>

    <div class="row g-4">
      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5>Pending Endorsements</h5>
            <h2 class="text-primary">{{ $pending ?? 0 }}</h2>
          </div>
        </div>
      </div>

      <div class="col-md-6">
        <div class="card shadow-sm">
          <div class="card-body">
            <h5>Approved Events</h5>
            <h2 class="text-success">{{ $approved ?? 0 }}</h2>
          </div>
        </div>
      </div>
    </div>
  </div>
@endsection
