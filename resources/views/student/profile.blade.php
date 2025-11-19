@extends('layouts/contentNavbarLayout')

@section('title', 'Registered Organizations')


@section('vendor-style')
  <link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endsection

@section('vendor-script')
  <script src="{{ asset('assets/vendor/libs/apex-charts/apexcharts.js') }}"></script>
@endsection

@section('page-script')
  <script src="{{ asset('assets/js/dashboards-analytics.js') }}"></script>

  <!-- SweetAlert2 -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  @if(session('login_success'))
    <script>
      Swal.fire({
        icon: 'success',
        title: 'Login Successful',
        text: @json(session('login_success')),
        showConfirmButton: false,
        timer: 2000
      });
    </script>
  @endif
@endsection

@section('content')
<div class="row">
  <!-- Left Card (Narrow) -->
  <div class="col-md-4 mb-4">
    <div class="card text-center">
      <div class="card-body">
        <!-- Rounded Avatar -->
        <img
          src="{{ asset('storage/profilepic/' .(Auth::user()->profilepic ?? 'default.jpg')) }}"
          alt="User Avatar"
          class="rounded-circle mb-2"
          width="320"
          height="320"
        >



        <h5 class="text-muted">
          {{ Auth::user()?->role }}
        </h5>
        <!-- User Name -->
        <h3 class="card-title mb-0 fw-bold">{{ Auth::user() ->first_name }} {{ Auth::user()->last_name }}</h3>
        <h8 class="card-title mb-2 ">{{ Auth::user()->email }} | {{ Auth::user()->ph_number }}  </h8>

      </div>
    </div>
  </div>

  <!-- Right Card (Wider) -->
<div class="col-md-6 mb-2">
  <div class="card">
    <div class="card-body">
     <h5 class="card-title text-center fw-bold">DETAILS</h5> <br>

      <p><strong>Org name:</strong> {{ Auth::user()->first_name }} </p>
      <p><strong>Adviser:</strong> {{ Auth::user()->last_name }}</p>
      <p><strong>President:</strong> {{ Auth::user()->age }} </p>
      <p><strong>V-pres:</strong> {{ Auth::user()->status }} </p>
      <p><strong>Seecretary:</strong> {{ Auth::user()->gender }}</p>
      <p><strong>Treasurer:</strong> {{ Auth::user()->province }}</p>
      <p><strong>Total Members:</strong> {{ Auth::user()->barangay }}</p>
      <p><strong>Members:</strong> {{ Auth::user()->municipality }}</p>
      <p><strong>Account created:</strong> {{ Auth::user()->created_at }}</p>
    </div>
  </div>
</div>

</div>





@endsection
