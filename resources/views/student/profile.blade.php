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

  <!-- LEFT: Signature & Signature upload -->
  <div class="col-lg-4">
    <div class="card">
      <div class="card-body text-center">
        <h5 class="card-title mb-3">Signature</h5>

        @if(Auth::user()->signature)
          <div class="mb-3">
            <img src="{{ asset('storage/' . Auth::user()->signature) }}" alt="Signature"
                 style="max-width:100%; max-height:200px; object-fit:contain;" class="border rounded">
          </div>
        @else
          <div class="mb-3 text-muted">No signature uploaded</div>
        @endif

        <form action="{{ route('user.uploadSignature') }}" method="POST" enctype="multipart/form-data">
          @csrf
          <div class="mb-2">
            <input type="file" name="signature" accept="image/*" class="form-control" required>
          </div>
          <button class="btn btn-primary w-100 btn-sm" type="submit">Upload Signature</button>
        </form>

        @if(Auth::user()->signature)
        <form action="{{ route('user.removeSignature') }}" method="POST" class="mt-2">
          @csrf
          @method('DELETE')
          <button class="btn btn-outline-danger w-100 btn-sm">Remove Signature</button>
        </form>
        @endif

      </div>
    </div>

    <!-- Optional: Remove profile picture button (if using profile pics elsewhere) -->

  </div>

  <!-- RIGHT: Details -->
  <div class="col-lg-8">
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title mb-3">Account Details</h5>

        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>Username</strong></p>
            <div class="text-muted">{{ Auth::user()->username }}</div>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Role</strong></p>
            <div class="text-muted">{{ Auth::user()->account_role }}</div>
          </div>
        </div>

        <div class="row mt-3">
          <div class="col-12">
            <p class="mb-1"><strong>Account created</strong></p>
            <div class="text-muted">{{ Auth::user()->created_at?->format('F d, Y h:i A') }}</div>
          </div>
        </div>
      </div>
    </div>

    <!-- User profile (readonly except contact fields) -->
    <div class="card mb-3">
      <div class="card-body">
        <h5 class="card-title mb-3">User Profile</h5>

        @php $p = Auth::user()->profile; @endphp

        <div class="row">
          <div class="col-md-6">
            <p class="mb-1"><strong>First Name</strong></p>
            <div class="text-muted">{{ Auth::user()->profile?->first_name }}</div>
          </div>
          <div class="col-md-6">
            <p class="mb-1"><strong>Middle Name</strong></p>
            <div class="text-muted">{{ $p?->middle_name }}</div>
          </div>
          <div class="col-md-6 mt-3">
            <p class="mb-1"><strong>Last Name</strong></p>
            <div class="text-muted">{{ $p?->last_name }}</div>
          </div>
          <div class="col-md-6 mt-3">
            <p class="mb-1"><strong>Suffix</strong></p>
            <div class="text-muted">{{ $p?->suffix }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <p class="mb-1"><strong>Sex</strong></p>
            <div class="text-muted">{{ $p?->sex }}</div>
          </div>

          <div class="col-md-6 mt-3">
            <p class="mb-1"><strong>Type</strong></p>
            <div class="text-muted">{{ $p?->type }}</div>
          </div>

          <div class="col-12 mt-3">
            <p class="mb-1"><strong>Address</strong></p>
            <div class="text-muted">{{ $p?->address }}</div>
          </div>
        </div>

        <hr>

        <!-- EDITABLE CONTACT INFO (only these fields) -->
        <h6 class="mb-3">Contact Information (Editable)</h6>
        <form action="{{ route('user.updateContact') }}" method="POST">
          @csrf
          @method('PUT')

          <div class="mb-2">
            <label class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email', $p?->email) }}" required>
          </div>

          <div class="mb-2">
            <label class="form-label">Contact number</label>
            <input type="text" name="contact_number" class="form-control" value="{{ old('contact_number', $p?->contact_number) }}" required>
          </div>

          <button type="submit" class="btn btn-primary">Save Contact Info</button>
        </form>
      </div>
    </div>

    <!-- Organization Details (readonly) -->
    <div class="card">
      <div class="card-body">
        <h5 class="card-title mb-3">Organization</h5>

        @if(Auth::user()->organization)
          <p class="mb-1"><strong>Name</strong></p>
          <div class="text-muted">{{ Auth::user()->organization->organization_name }}</div>

          <p class="mb-1 mt-3"><strong>Type</strong></p>
          <div class="text-muted">{{ Auth::user()->organization->organization_type }}</div>

          <p class="mb-1 mt-3"><strong>Description</strong></p>
          <div class="text-muted">{{ Auth::user()->organization->description }}</div>

          <p class="mb-1 mt-3"><strong>Status</strong></p>
          <div class="text-muted">{{ Auth::user()->organization->status }}</div>

          <p class="mb-1 mt-3"><strong>Created</strong></p>
          <div class="text-muted">{{ Auth::user()->organization->created_at?->format('F d, Y') }}</div>
        @else
          <div class="text-muted">No organization assigned.</div>
        @endif
      </div>
    </div>

  </div>
</div>
@endsection
