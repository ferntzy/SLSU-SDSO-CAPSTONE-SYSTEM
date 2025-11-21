@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'Registered Organizations')

@section('head')
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
@endsection
    @include("admin.organizations.orgjs")
@section('content')
<div class="{{ $container }}">
    <div class="card shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Registered Organizations</h5>
            <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addOrgModal">
                <i class="ti ti-plus me-1"></i> Add Organization
            </button>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover text-center align-middle">
                <thead class="table-light">
                    <tr>
                        <th>Organization Name</th>
                        <th>Type</th>
                        <th>Members</th>
                        <th>Adviser</th>
                        <th>Officer</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($organizations as $org)
                    <tr>
                        <td>{{ $org->organization_name }}</td>
                        <td>{{ $org->organization_type }}</td>
                        <td>{{ $org->members_count }}</td>
                        <td>
                            {{ optional($org->adviser)->profile->first_name ?? '' }}
                            {{ optional($org->adviser)->profile->last_name ?? '' }}
                        </td>
                        <td>
                            @if($org->officers->count())
                                {{ $org->officers->first()->profile->first_name ?? '' }}
                                {{ $org->officers->first()->profile->last_name ?? '' }}
                            @else
                                N/A
                            @endif
                        </td>

                        <td>
                            <span class="badge bg-label-{{ $org->status == 'Active' ? 'success' : 'secondary' }}">
                                {{ $org->status }}
                            </span>
                        </td>


                        <td>
                            <button class="btn btn-sm btn-outline-primary view-details-btn"
                                    data-id="{{ $org->organization_id }}">
                                View
                            </button>
                            <button class="btn btn-sm btn-outline-warning edit-org-btn"
                                    data-id="{{ $org->organization_id }}"
                                    data-name="{{ $org->organization_name }}"
                                    data-type="{{ $org->organization_type }}"
                                    data-status="{{ $org->status }}"
                                    data-advisor="{{ $org->advisor_name }}"
                                    data-description="{{ $org->description }}"
                                    data-members="{{ $org->members_count }}">
                                <i class="ti ti-edit me-1"></i> Edit
                            </button>
                            <button class="btn btn-sm btn-outline-danger delete-org-btn"
                                    data-id="{{ $org->organization_id }}"
                                    data-name="{{ $org->organization_name }}">
                                <i class="ti ti-trash me-1"></i> Delete
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted">No organizations found.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

{{-- ADD ORGANIZATION MODAL --}}
<div class="modal fade" id="addOrgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('organizations.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title">Add Organization</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Organization Name</label>
                        <input type="text" name="organization_name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Organization Type</label>
                        <select name="organization_type" class="form-select" required>
                            <option value="">Select Organization Type</option>
                            <option>Academic Organization</option>
                            <option>Non-Academic Organization</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- VIEW DETAILS MODAL --}}
<div class="modal fade" id="orgDetailsModal" tabindex="-1" aria-labelledby="orgDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Organization Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <!-- Organization Logo -->
                <div class="text-center mb-3">
                    <img id="orgLogo" src="" alt="Organization Logo" class="img-fluid rounded" style="max-height: 120px;">
                </div>

                <!-- Basic Info -->
                <h4 id="orgName" class="fw-bold text-primary mb-2"></h4>
                <p id="orgType" class="text-muted mb-3"></p>

                <p><strong>Description:</strong></p>
                <p id="orgDescription" class="mb-4"></p>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p><strong>Members:</strong> <span id="orgMembers">—</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>Adviser:</strong> <span id="orgAdvisor">—</span></p>
                    </div>
                </div>
                <p><strong>Created At:</strong> <span id="orgCreatedAt">—</span></p>
                <hr>
                <h5 class="fw-bold mb-3">Select Student Organization Officer and Adviser</h5>
                <div class="row">
                    <div class="col-md-4 mb-2">
                        <p><strong>Officer:</strong> <span id="officer_id">—</span></p>
                    </div>
                    <div class="col-md-4 mb-2">
                        <p><strong>Contact:</strong> <span id="contact_number">—</span></p>
                    </div>
                    <div class="col-md-4 mb-2">
                        <p><strong>Email:</strong> <span id="contact_email">—</span></p>
                    </div>
                </div>
                <hr>
                <p><strong>Status:</strong>
                    <span id="orgStatus" class="badge">—</span>
                </p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
{{-- EDIT ORGANIZATION MODAL --}}
<div class="modal fade" id="editOrgModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form id="editOrgForm" method="POST">
        @csrf
        @method('PUT')
        <div class="modal-header bg-warning text-white">
          <h5 class="modal-title">Edit Organization</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="editOrgId">
          <div class="mb-3">
            <label class="form-label">Organization Name</label>
            <input type="text" name="organization_name" class="form-control" id="editOrgName" required>
          </div>
          <div class="mb-3">
            <label class="form-label">Organization Type</label>
            <select name="organization_type" class="form-select" id="editOrgType" required>
                <option value="">Select Organization Type</option>
                <option>Academic Organization</option>
                <option>Non-Academic Organization</option>
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" rows="3" id="editOrgDescription"></textarea>
          </div>
          <div class="mb-3">
            <label class="form-label">Officer (Optional)</label>
            <select name="officer_id" class="form-select">
                <option value=""></option>
                @foreach($officers as $officer)
                    <option value="{{ $officer->user_id }}" {{ isset($organization_officer) && $organization_officer->user_id == $student->user_id ? 'selected' : '' }}>
                        {{ $officer->profile->first_name }} {{ $officer->profile->last_name }}
                    </option>
                @endforeach
            </select>
          </div>
          <div class="mb-3">
            <label class="form-label">Adviser (Optional)</label>
            <select name="adviser_id" class="form-select" id="editAdviser">
                <option value=""></option>
                @foreach($advisers as $adviser)
                    <option value="{{ $adviser->user_id }}">
                        {{ $adviser->profile->first_name }} {{ $adviser->profile->last_name }}
                    </option>
                @endforeach
            </select>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="submit" class="btn btn-warning">Update</button>
        </div>
      </form>
    </div>
  </div>
</div>




{{-- SweetAlert for success --}}
@section('page-script')
<script>
document.addEventListener("DOMContentLoaded", () => {
    const msg = document.querySelector('meta[name="success-message"]')?.content;
    if(msg){
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: msg,
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }
     $(document).on('click', '.edit-org-btn', function() {
          const orgId = $(this).data('id');

          $('#editOrgId').val(orgId);
          $('#editOrgName').val($(this).data('name') || '');
          $('#editOrgType').val($(this).data('type') || '');
          $('#editOrgDescription').val($(this).data('description') || '');
          $('#editOfficer').val($(this).data('officer') || '');
          $('#editAdviser').val($(this).data('adviser') || '');
          $('#editOrgForm').attr('action', "{{ route('organizations.update', '') }}/" + orgId);

          const modal = new bootstrap.Modal(document.getElementById('editOrgModal'));
          modal.show();
      });


    // Optional: Add your JS for view/edit/delete buttons here
});
</script>
@endsection
@endsection
