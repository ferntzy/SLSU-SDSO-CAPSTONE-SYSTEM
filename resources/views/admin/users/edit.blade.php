@php
  $container = 'container-xxl';
  $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@include("admin.users.js")
@section('title', 'Edit User')

@section('content')
  <div class="{{ $container }}">
    <div class="card mb-4">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Edit User Account</h5>
        <a href="{{ route('users.index') }}" class="btn btn-secondary">Back</a>
      </div>

      <div class="card-body">
        <form method="POST" action="{{ route('users.update', $user->user_id) }}">
          @csrf
          @method('PUT')

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ $user->email }}" required>
          </div>

        <div class="mb-3 position-relative">
            <label class="form-label">Password (Leave blank to keep current)</label>
            <div class="input-group">
               <input type="password" name="password" class="form-control password-field" >
              <span class="input-group-text toggle-password" style="cursor:pointer;">
                <i class="bi bi-eye-slash"></i>
              </span>

            </div>
          </div>


          <div class="mb-3">
            <label class="form-label">Account Role</label>
            <select name="account_role" class="form-select" required>
              @foreach(['Student_Organization', 'SDSO_Head', 'Faculty_Adviser', 'VP_SAS', 'SAS_Director', 'BARGO', 'admin'] as $role)
                <option value="{{ $role }}" {{ $user->account_role == $role ? 'selected' : '' }}>{{ $role }}</option>
              @endforeach
            </select>
          </div>

          <button type="submit" class="btn btn-success">Update</button>
        </form>
      </div>
    </div>
  </div>
@endsection
