@php
  $container = 'container-xxl';
  $containerNav = 'container-xxl';
@endphp
@extends('layouts.contentNavbarLayout')
@section('title', 'Add New Profile')

<head>
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/organization.css') }}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(session('success'))
<meta name="success-message" content="{{ session('success') }}">
@endif
</head>

@section('content')
<div class="row">
  <div class="col-xl">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">NEW PROFILE REGISTRATION</h5>
      </div>
      <div class="card-body">
        <form action="{{ route('profiles.store') }}" method="POST">
          @csrf

          <!-- FULL NAME -->
          <div class="form text-dark mb-3">FULL NAME</div>

          <div class="row">

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" name="first_name" class="form-control" placeholder="First Name">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" name="last_name" class="form-control" placeholder="Last Name">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" name="middle_name" class="form-control" placeholder="Middle Name">
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <select name="suffix" class="form-control">
                  <option value=""></option>
                  <option value="JR">Jr</option>
                  <option value="SR">Sr</option>
                  <option value="II">II</option>
                  <option value="III">III</option>
                  <option value="IV">IV</option>
                  <option value="V">V</option>
                  <option value="VI">VI</option>
                  <option value="VII">VII</option>
                  <option value="VIII">VIII</option>
                  <option value="IX">IX</option>
                  <option value="X">X</option>
                </select>
              </div>
            </div>

          </div>

          <!-- CONTACT INFORMATION -->
          <div class="form text-dark mb-3">CONTACT INFORMATION</div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                <input type="text" name="email" class="form-control" placeholder="Email">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-phone"></i></span>
                <input type="text" name="contact_number" class="form-control" placeholder="Contact Number">
              </div>
            </div>
          </div>

          <!-- ADDRESS -->
          <div class="mb-4">
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="mdi mdi-map-marker-outline"></i></span>
              <input type="text" name="address" class="form-control" placeholder="Address">
            </div>
          </div>

          <!-- SEX + TYPE -->
          <div class="row">

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-gender-male-female"></i></span>
                <select name="sex" class="form-control">
                  <option value=""></option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-badge-horizontal-outline"></i></span>
                <select name="type" class="form-control">
                  <option selected disabled>Select User Profile Type</option>
                  <option value="Student">Student</option>
                  <option value="Employee">Employee</option>
                </select>
              </div>
            </div>

          </div>

          <div class="d-flex justify-content-between mt-4">
            <a href="{{ url()->previous() }}" class="btn btn btn-outline-warning">Back</a>
            <button type="submit" class="btn btn-primary">Submit</button>
          </div>

        </form>

      </div>
    </div>
  </div>
</div>
@endsection
