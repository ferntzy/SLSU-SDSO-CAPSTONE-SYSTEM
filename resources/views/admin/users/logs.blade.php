@php
  $container = 'container-xxl';
   $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'User Activity Logs')

@section('content')

<div class="d-flex align-items-center mb-3">
    <span class="text-secondary fs-5 fw-normal">User Management</span>
    <span class="mx-2 text-secondary">|</span>
    <i class="mdi mdi-home-outline text-secondary fs-6"></i>
    <span class="mx-1 text-secondary" style="font-size: 10px;">&gt;</span>
    <span class="ms-2 text-muted fs-6">User Logs</span>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">

        <div class="header-title">
            <h5 class="mb-0 text-dark">USER ACTIVITY LOGS</h5>
        </div>

        <div class="card-action">
            <div class="input-group">
                <input type="text" id="searchAccountLogs" class="form-control" placeholder="Search Account Logs">
                <button class="input-group-text" id="btnSearchLogs">
                    <i class="mdi mdi-account-search-outline"></i>
                </button>
            </div>
        </div>

    </div>

   <div x-data="logsTable()">
    <div id="logsContainer">
        @include('admin.users.logs-list')
    </div>
</div>

</div>

@endsection

@section('page-script')
@include('admin.users.js')
@endsection
