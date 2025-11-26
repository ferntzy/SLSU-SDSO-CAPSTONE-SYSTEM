@php
    $container = 'container-xxl';
    $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'Registered Organizations')

@section('content')
<div class="d-flex align-items-center mb-3">
    <span class="text-secondary fs-5 fw-normal">Organizations</span>
    <span class="mx-2 text-secondary">|</span>
    <i class="mdi mdi-home-outline text-secondary fs-6"></i>
    <span class="mx-1 text-secondary" style="font-size: 10px;">&gt;</span>
    <span class="ms-2 text-muted fs-6">All Organization</span>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="header-title">
            <h5 class="mb-0 text-dark">REGISTERED ORGANIZATIONS</h5>
        </div>
        <div class="card-action">

          <div class = "input-group">
            <input type="text" id="searchOrg" class="form-control" placeholder="Search Organization">
            <button class="input-group-text" id = "btnSearchOrg"><i class="mdi mdi-account-search-outline"></i></button>

             <a href="#" class="btn btn-primary btn-sm"
               data-bs-toggle="modal" data-bs-target="#createOrgModal">
                <i class="ti ti-plus"></i> Add Organization
            </a>
          </div>

        </div>
    </div>
    <div class="table-responsive text-nowrap" id="orglist">
            @include('admin.organizations.list-organization')
    </div>
</div>

@include('admin.organizations.create-organization')
@endsection

@section('page-script')
    @include('admin.organizations.orgjs')
@endsection
