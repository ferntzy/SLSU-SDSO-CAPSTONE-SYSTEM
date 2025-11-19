@php $container = 'container-xxl'; @endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'SAS Director Dashboard')

@section('content')
  <div class="{{ $container }} py-4">
    <h4 class="fw-bold mb-4">SAS Director / College Dean Dashboard</h4>

    <div class="alert alert-warning">Awaiting documents from SDSO.</div>

    <div class="card shadow-sm border-0">
      <div class="card-body">
        <h5>Recent Approvals</h5>
        <ul>
          @foreach($recentApprovals ?? [] as $approval)
            <li>{{ $approval->event_title }} — Approved on {{ $approval->approved_at }}</li>
          @endforeach
        </ul>
      </div>
    </div>
  </div>
@endsection