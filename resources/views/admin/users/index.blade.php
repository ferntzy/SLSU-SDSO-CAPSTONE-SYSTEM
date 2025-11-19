@php
  $container = 'container-xxl';
  $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@include('admin.users.js')

@section('title', 'Users Management')

<style>
  th a {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    color: inherit;
  }

  th a:hover {
    text-decoration: underline;
  }
</style>

@section('content')
  <div class="{{ $container }}">


@if(session('error'))
    <script>
        Swal.fire({
            title: "Error!",
            text: "{{ session('error') }}",
            icon: "error",
            draggable: true
        });
    </script>
@endif




 @if(session('success'))
      <script>
        Swal.fire({
          title: "Account Updated!",
          text: "{{ session('success') }}",
          icon: "success",
          draggable: true
        });
        </script>
      @endif
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Accounts</h5>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
          <i class="bx bx-plus"></i> Create Account
        </a>
      </div>



      <div class="table-responsive text-nowrap">
        <table class="table table-hover mb-0">
          <thead class="table-light">
            <tr>
              {{-- Static headers --}}
              <th>ID</th>
              <th>Username</th>
              <th>Firstname</th>
              <th>Lastname</th>
              <th>Type</th>


              {{-- Sortable: Role --}}
              <th>
                <a href="{{ route('users.index', ['sort' => 'account_role', 'direction' => ($sortField === 'account_role' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark fw-semibold">
                  Role
                  @if($sortField === 'account_role')
                    @if($sortDirection === 'asc')
                      ▲
                    @else
                      ▼
                    @endif
                  @endif
                </a>
              </th>

              {{-- Sortable: Date Created --}}
              <th>
                <a href="{{ route('users.index', ['sort' => 'created_at', 'direction' => ($sortField === 'created_at' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark fw-semibold">
                  Date Created
                  @if($sortField === 'created_at')
                    @if($sortDirection === 'asc')
                      ▲
                    @else
                      ▼
                    @endif
                  @endif
                </a>
              </th>

              {{-- Sortable: Time --}}
              <th>
                <a href="{{ route('users.index', ['sort' => 'created_at', 'direction' => ($sortField === 'created_at' && $sortDirection === 'asc') ? 'desc' : 'asc']) }}"
                   class="text-decoration-none text-dark fw-semibold">
                  Time
                  @if($sortField === 'created_at')
                    @if($sortDirection === 'asc')
                      ▲
                    @else
                      ▼
                    @endif
                  @endif
                </a>
              </th>

              {{-- Static header: Actions --}}
              <th>Actions</th>
            </tr>
          </thead>

          <tbody>
            @forelse($users as $user)
              <tr>
                <td>{{ $user->user_id }}</td>
                <td>{{ $user->username }}</td>
                <td>{{ $user->profile->first_name ?? 'none' }}</td>
                <td>{{ $user->profile->last_name ?? 'none' }}</td>
                <td>{{ $user->profile->type ?? 'none' }}</td>
                <td><span class="badge bg-label-info">{{ $user->account_role }}</span></td>
                <td>{{ $user->created_at->format('Y-m-d') }}</td>
                <td>{{ $user->created_at->format('H:i:s') }}</td>
                <td>
                {{-- <a href="#" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#viewProfileModal">👁️</a> --}}

                  <a href="javascript:void(0);" class="view-profile-btn"
                    data-user-id="{{ $user->user_id }}"
                    data-first-name="{{ $user->profile?->first_name ?? 'none' }}"
                    data-middle-name="{{ $user->profile?->middle_name ?? 'none' }}"
                    data-last-name="{{ $user->profile?->last_name ?? 'none' }}"
                    data-email="{{ $user->email ?? 'none' }}"
                    data-contact="{{ $user->profile?->contact_number ?? 'none' }}"
                    data-address="{{ $user->profile?->address ?? 'none' }}"
                    data-office="{{ $user->profile?->office ?? 'none' }}"
                    data-status="{{ $user->status ?? 'Active' }}"
                    data-role="{{ $user->account_role ?? 'none' }}"
                    data-avatar="{{ asset($user->profile?->profile_picture_path ?? 'assets/images/slsu_logo.png') }}"
                    data-joined="{{ $user->created_at->format('Y-m-d') ?? '' }}"
                  >
                    👁️
                  </a>



                    <!-- Edit icon triggers modal -->
                  {{-- <a href="{{ route('users.edit', $user->user_id) }}" title="Edit" class="me-2">✏️</a> --}}
                  <a href="javascript:void(0);"
                    class="me-2 editUserBtn"
                    data-user-id="{{ $user->user_id }}"
                    data-username="{{ $user->username }}"
                    data-email="{{ $user->email }}"
                    data-role="{{ $user->account_role }}"
                    title="Edit">✏️</a>

                  <a href="javascript:void(0);"
                    data-bs-toggle="modal"
                    data-bs-target="#confirmDeleteModal"
                    data-user-id="{{ $user->user_id }}"
                    data-username="{{ $user->username }}"
                    title="Delete">🗑️</a>
                </td>


              </tr>
            @empty
              <tr>
                <td colspan="7" class="text-center text-muted">No users found.</td>
              </tr>
            @endforelse
          </tbody>
        </table>
      </div>
    </div>
  </div>


 {{---VIEW PROFILE IN THE USER LIST--}}
<div class="modal fade" id="viewProfileModal" tabindex="-1" aria-labelledby="viewProfileModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" style="max-width: 700px;">
    <div class="modal-content" style="height: 0%;">

      <div class="modal-header">
        <h5 class="modal-title" id="viewProfileModalLabel">User Profile</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body" style="overflow-y: auto;">
        <div class="row">
          <!-- Left: Avatar -->
          <div class="col-md-4 text-center mb-3">
            <img class="rounded-circle img-fluid" id="modal-avatar"
                 style="width: 180px; height: 180px; object-fit: cover;">
            <h4 id="modal-name" class="mt-2"></h4>
            <p class="text-muted" id="modal-role"></p>
          </div>

          <!-- Right: User Info -->
          <div class="col-md-8">
            <p><strong>First Name:</strong> <span id="modal-first-name"></span></p>
            <p><strong>Middle Name:</strong> <span id="modal-middle-name"></span></p>
            <p><strong>Last Name:</strong> <span id="modal-last-name"></span></p>
            <p><strong>Email:</strong> <span id="modal-email"></span></p>
            <p><strong>Contact #:</strong> <span id="modal-contact"></span></p>
            <p><strong>Address:</strong> <span id="modal-address"></span></p>
            <p><strong>Office:</strong> <span id="modal-office"></span></p>
            <p><strong>Status:</strong> <span id="modal-status"></span></p>
            <p><strong>Joined:</strong> <span id="modal-joined"></span></p>
          </div>
        </div>
      </div>

      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
      </div>

    </div>
  </div>
</div>


<script>
  $(document).ready(function() {
  $(document).on('click', '.view-profile-btn', function(e) {
    e.preventDefault();
    const button = $(this);

    // Default Avatar Fallback
    const avatarUrl = button.data('avatar') || '/images/user_profile/calendar.png'; // Replace with your actual default image path
    $('#modal-avatar').attr('src', avatarUrl);

    // Name display below avatar
    const firstName = button.data('first-name') || '';
    const lastName = button.data('last-name') || '';
    $('#modal-name').text(`${firstName} ${lastName}`);

    // Role
    $('#modal-role').text(button.data('role') || '');

    // User details on the right side
    $('#modal-first-name').text(firstName || 'none');
    $('#modal-middle-name').text(button.data('middle-name') || 'none');
    $('#modal-last-name').text(lastName || 'none');
    $('#modal-email').text(button.data('email') || 'none');
    $('#modal-contact').text(button.data('contact') || 'none');
    $('#modal-address').text(button.data('address') || 'none');
    $('#modal-office').text(button.data('office') || 'none');
    $('#modal-status').text(button.data('status') || 'Active');
    $('#modal-joined').text(button.data('joined') || '');

    $('#viewProfileModal').modal('show');
  });
});

</script>

  {{-- <div class="modal fade" id="viewProfileModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

              <div class="modal-header">
                  <h5 class="modal-title">User Profile</h5>
                  <button class="btn-close" data-bs-dismiss="modal"></button>
              </div>

              <div class="modal-body">
                  <div id="modalContent">
                      <!-- AJAX Loaded Content Here -->
                  </div>
              </div>

          </div>
      </div>
  </div> --}}

  <script>

    $(document).on('click', '.view-profile-btn', function (e) {
    e.preventDefault();

    let userId = $(this).data('user-id');

    $.ajax({
        url: "/users/" + userId + "/profile",
        type: "GET",
        success: function (response) {
            $("#modalContent").html(response);
            $("#viewProfileModal").modal("show");
        }
    });
});

  </script>



<!-- Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form method="POST" id="editUserForm">
      @csrf
      @method('PUT')
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="editUserModalLabel">Edit User Account</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">

          <input type="hidden" name="id" id="edit-user-id">

          <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" id="edit-username" class="form-control" required>
          </div>

          <div class="mb-3">
            <label class="form-label">Email</label>
            <input type="email" name="email" id="edit-email" class="form-control" required>
          </div>

          <div class="mb-3 position-relative">
            <label class="form-label">Password (Leave blank to keep current)</label>
            <div class="input-group">
              <input type="password" name="password" id="edit-password" class="form-control password-field">
              <span class="input-group-text toggle-password" style="cursor:pointer;">
                <i class="bi bi-eye-slash"></i>
              </span>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Account Role</label>
            <select name="account_role" id="edit-role" class="form-select" required>
              @foreach(['Student_Organization', 'SDSO_Head', 'Faculty_Adviser', 'VP_SAS', 'SAS_Director', 'BARGO', 'admin'] as $role)
                <option value="{{ $role }}">{{ $role }}</option>
              @endforeach
            </select>
          </div>

        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-success">Update</button>
        </div>
      </div>
    </form>
  </div>
</div>





</div>







  {{-- Delete Confirmation Modal --}}
  <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteLabel" aria-hidden="true">
    <div class="modal-dialog">
      <form method="POST" id="deleteUserForm">
        @csrf
        @method('DELETE')
        <div class="modal-content">
          <div class="modal-header bg-danger text-white">
            <h5 class="modal-title" id="confirmDeleteLabel">Confirm Delete</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <p id="deleteMessage">Are you sure you want to delete this account?</p>

         <div class="mb-3">
            <label for="adminPassword" class="form-label">Enter your admin password to confirm:</label>
            <div class="input-group">
              <input type="password" class="form-control" id="adminPassword" name="admin_password" required>
              <span class="input-group-text toggle-password" style="cursor:pointer;">
                <i class="bx bx-show"></i>
              </span>
            </div>
          </div>


            <div id="passwordError" class="text-danger d-none">Incorrect password. Please try again.</div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-danger">Delete</button>
          </div>
        </div>
      </form>


    </div>
  </div>

@endsection
