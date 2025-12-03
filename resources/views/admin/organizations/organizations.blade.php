@php
    $container = 'container-xxl';
    $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'Registered Organizations')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <div class="header-title">
            <h5 class="mb-0 text-dark">REGISTERED ORGANIZATIONS</h5>
        </div>
        <div class="card-action">
            <a href="#" class="btn btn-info btn-sm"
               data-bs-toggle="modal" data-bs-target="#createOrgModal">
                <i class="ti ti-plus"></i> Add Organization
            </a>
        </div>
    </div>
    <div class="table-responsive text-nowrap" id="orglist">
            @include('admin.organizations.list-organization')
    </div>
</div>
@include('admin.organizations.add-member')
@include('admin.organizations.add-officer')
@include('admin.organizations.create-organization')
@endsection

@section('vendor-script')
    @include('admin.organizations.orgjs')
@endsection
