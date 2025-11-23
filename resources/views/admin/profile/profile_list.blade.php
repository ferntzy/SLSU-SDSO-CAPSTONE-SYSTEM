@php
  $container = 'container-xxl';
  $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'User Profile List')

@section('content')
<!-- Hoverable Table rows -->

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">

    <div class="header-title">
      <h5 class="mb-0 text-dark">USER PROFILES</h5>
    </div>
    <div class="card-action">
      <div class="input-group">
       <input type="text" id="searchProfile" class="form-control" placeholder="Search Profile">
      <button class="input-group-text" id = "btnSearchProfile"><i class="mdi mdi-account-search-outline"></i></button>

        <a href="#" class="input-group-text bg-primary text-white"
           data-bs-toggle="modal" data-bs-target="#createProfileModal">
            <span class="mdi mdi-account-plus"></span>
        </a>
      </div>
    </div>

  </div>
  <div class="table-responsive text-nowrap" id = "profilelist">
      @include('admin.profile.profile-list')
  </div>
</div>
@include('admin.profile.create_profile')
@include('admin.profile.view-profile')
@if(session('success') && session('highlight_id'))
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    const highlightId = {{ session('highlight_id') }};
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: '{{ session('success') }}',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    }).then(() => {
        // Remove the highlight class after clicking OK
        const row = document.querySelector(`tr[data-id='${highlightId}']`);
        if(row) {
            row.classList.remove('table-success');
        }
    });
</script>
@endif

@endsection
@section('page-script')
@include('admin.profile.js')
@endsection

