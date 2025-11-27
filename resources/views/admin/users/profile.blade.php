@extends('layouts/contentNavbarLayout')

@section('title', 'My Profile')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
Swal.fire({
  icon: 'success',
  title: 'Success',
  text: @json(session('success')),
  timer: 2000,
  showConfirmButton: false
});
</script>
@endif

@if(session('error'))
<script>
Swal.fire({
  icon: 'error',
  title: 'Error',
  text: @json(session('error')),
  timer: 2500,
  showConfirmButton: false
});
</script>
@endif
@endsection

@section('content')
<div class="row g-4">

  <!-- Breadcrumb -->
  <div class="d-flex align-items-center mb-3">
    <span class="text-secondary fs-5 fw-normal">Admin</span>
    <span class="mx-2 text-secondary">|</span>
    <i class="mdi mdi-home-outline text-secondary fs-6"></i>
    <span class="mx-1 text-secondary" style="font-size: 10px;">&gt;</span>
    <span class="ms-2 text-muted fs-6">Profile</span>
  </div>


  <!-- LEFT COLUMN: Signature Only -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body text-center">
        <h5 class="card-title mb-3">Signature</h5>

        @if(Auth::user()->signature)
          <div class="mb-3">
            <img src="{{ asset('storage/' . Auth::user()->signature) }}"
                 alt="Signature"
                 class="border rounded"
                 style="max-width:100%; max-height:200px; object-fit:contain;">
          </div>
        @else
          <div class="mb-3 text-muted">No signature uploaded</div>
        @endif

        <!-- Upload Signature -->
        <form action="{{ route('admin.uploadSignature') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <input type="file" name="signature" accept="image/*" class="form-control mb-2" required>
          <button class="btn btn-primary w-100 btn-sm">Upload Signature</button>
        </form>

        @if(Auth::user()->signature)
        <!-- Remove Signature -->
        <form action="{{ route('admin.removeSignature') }}" method="POST" class="mt-2" >
          @csrf
          @method('DELETE')
          <button class="btn btn-outline-danger w-100 btn-sm">Remove Signature</button>
        </form>
        @endif

      </div>
    </div>
  </div>


  <!-- RIGHT COLUMN -->
  <div class="col-lg-8">

    <!-- Account Details -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title mb-3">Account Details</h5>

        <div class="row">
          <div class="col-md-6">
            <strong>Username</strong>
            <div class="text-muted">{{ Auth::user()->username }}</div>
          </div>

          <div class="col-md-6">
            <strong>Role</strong>
            <div class="text-muted">{{ Auth::user()->account_role }}</div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12">
            <strong>Account Created</strong>
            <div class="text-muted">
              {{ Auth::user()->created_at?->format('F d, Y - h:i A') }}
            </div>
          </div>
        </div>
      </div>
    </div>


    <!-- User Profile (Readonly except contact fields) -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title mb-3">User Profile</h5>

        @php $p = Auth::user()->profile; @endphp

        <div class="row">
          <div class="col-md-6">
            <strong>First Name</strong>
            <div class="text-muted">{{ $p?->first_name }}</div>
          </div>

          <div class="col-md-6">
            <strong>Middle Name</strong>
            <div class="text-muted">{{ $p?->middle_name }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <strong>Last Name</strong>
            <div class="text-muted">{{ $p?->last_name }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <strong>Suffix</strong>
            <div class="text-muted">{{ $p?->suffix }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <strong>Sex</strong>
            <div class="text-muted">{{ $p?->sex }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <strong>Type</strong>
            <div class="text-muted">{{ $p?->type }}</div>
          </div>

          <div class="col-12 mt-3">
            <strong>Address</strong>
            <div class="text-muted">{{ $p?->address }}</div>
          </div>
        </div>

        <hr>

        <!-- Editable Contact Info -->
        <h6 class="mb-3">Contact Information (Editable)</h6>

        <form action="{{ route('user.updateContact') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control"
                   value="{{ old('email', $p?->email) }}" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Contact Number</label>
            <input type="text" name="contact_number" class="form-control"
                   value="{{ old('contact_number', $p?->contact_number) }}" required>
          </div>

          <button type="submit" class="btn btn-primary">Save Contact Info</button>
        </form>

      </div>
    </div>

  </div>

</div>
@endsection
