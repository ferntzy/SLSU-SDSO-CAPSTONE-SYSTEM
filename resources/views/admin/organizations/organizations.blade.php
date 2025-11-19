@php
  $container = 'container-xxl';
@endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'Registered Organizations')
<head>
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>
<link rel="stylesheet" href="{{ asset('assets/css/organization.css') }}">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(session('success'))
<meta name="success-message" content="{{ session('success') }}">
@endif
@include("admin.organizations.orgjs")
</head>

@section('content')
  <div class="{{ $container }}">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Registered Organizations</h5>
        <button class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#addOrgModal">
          <i class="ti ti-plus me-1"></i> Add Organization
        </button>
      </div>

      <div class="card-body">
        <div class="row g-4">
          @foreach ($organizations as $org)
            <div class="col-md-6 col-lg-4">
              <div class="card h-100 border-0 shadow-sm hover-card">
                <div class="card-body">
                  <div class="d-flex justify-content-between align-items-center mb-3">
                    <div class="avatar avatar-md bg-label-primary me-2">
                      <i class="ti ti-building fs-4 text-primary"></i>
                    </div>
                    <span class="badge bg-label-{{ $org['status'] == 'Active' ? 'success' : 'secondary' }}">
                      {{ $org['status'] }}
                    </span>
                  </div>

                  <h5 class="fw-semibold text-dark mb-1">{{ $org['name'] }}</h5>
                  <p class="text-muted small mb-3">{{ $org['type'] }}</p>

                  <div class="d-flex justify-content-between align-items-center">
                    <div>
                      <small class="text-muted d-block">Members</small>
                     <span class="fw-semibold">{{ $org->members_count }}</span>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Adviser</small>
                        <span class="fw-semibold">{{ $org->advisor }}</span>
                    </div>

                  </div>
                </div>

                <div class="card-footer bg-transparent border-top text-center d-flex gap-1">
                    <button
                      class="btn btn-sm btn-outline-primary flex-fill view-details-btn"
                      data-id="{{ $org->organization_id }}"
                    >
                      View
                  </button>
                  <button class="btn btn-sm btn-outline-warning flex-fill edit-org-btn" data-id="{{ $org['id'] }}"
                    data-name="{{ $org['name'] }}" data-type="{{ $org['type'] }}" data-members="{{ $org['members'] }}"
                    data-status="{{ $org['status'] }}" data-advisor="{{ $org['advisor'] }}"
                    data-description="{{ $org['description'] }}">
                    <i class="ti ti-edit me-1"></i> Edit
                  </button>
                  <button class="btn btn-sm btn-outline-danger flex-fill delete-org-btn"
                      data-id="{{ $org->organization_id }}" data-name="{{ $org->organization_name }}">
                      <i class="ti ti-trash me-1"></i> Delete
                  </button>
                </div>
              </div>
            </div>
          @endforeach
        </div>
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

                  <h5 class="fw-bold mb-3">Officer Info</h5>
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



  {{-- ADD ORGANIZATION MODAL --}}
    <div class="modal fade" id="addOrgModal" tabindex="-1">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
          <form id="addOrgForm" action="{{ route('organizations.store') }}" method="POST">
            @csrf

            <!-- Modal Header -->
            <div class="modal-header bg-primary text-white">
              <h5 class="modal-title">Add Organization</h5>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">

              <!-- Organization Fields -->
              <div class="mb-3">
                <label class="form-label">Organization Name</label>
                <input type="text" name="organization_name" class="form-control" required>
              </div>

              <div class="mb-3">
                <label class="form-label">Type</label>
                <select name="organization_type" class="form-select" required>
                  <option>Academic Organization</option>
                  <option>Government Organization</option>
                  <option>Civic Organization</option>
                  <option>Cultural Organization</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Adviser</label>
                <select name="adviser_name" class="form-select">
                  <option value=""></option>
                  @foreach ($advisers as $adviser)
                    <option value="{{ $adviser->user_id }}">
                      {{ $adviser->profile->first_name ?? '' }} {{ $adviser->profile->last_name ?? '' }}
                    </option>
                  @endforeach
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                  <option>Active</option>
                  <option>Inactive</option>
                </select>
              </div>

              <div class="mb-3">
                <label class="form-label">Description</label>
                <textarea name="description" class="form-control" rows="3"></textarea>
              </div>

              <!-- Officer Fields -->
              <hr>
              <h6 class="fw-semibold">Organization Officer</h6>
              <div class="officer-group mb-3">
                <div class="mb-2">
                  <label for="officer_user_id" class="form-label">Officer</label>
                  <select name="officer_id" id="officer_id" class="form-select">
                      <option value=""></option>
                      @foreach($officers as $officer)
                          <option value="{{ $officer->user_id }}">
                              {{ $officer->profile->first_name }} {{ $officer->profile->last_name }}
                          </option>
                      @endforeach
                  </select>
                </div>

                <div class="mb-2">
                  <label class="form-label">Email</label>
                  <input type="email" name="contact_email" class="form-control" >
                </div>

                <div class="mb-2">
                  <label class="form-label">Contact Number</label>
                  <input type="text" name="contact_number" class="form-control" >
                </div>
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



  {{-- EDIT ORGANIZATION MODAL (same structure as add, prefilled dynamically) --}}
  <div class="modal fade" id="editOrgModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <form id="editOrgForm">
          <div class="modal-header bg-warning text-white">
            <h5 class="modal-title">Edit Organization</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
          </div>
          <div class="modal-body">
            <input type="hidden" id="editOrgId">
            <div class="mb-3">
              <label class="form-label">Organization Name</label>
              <input type="text" class="form-control" id="editOrgName">
            </div>
            <div class="mb-3">
              <label class="form-label">Type</label>
              <input type="text" class="form-control" id="editOrgType">
            </div>
            <div class="mb-3">
              <label class="form-label">Adviser</label>
              <input type="text" class="form-control" id="editOrgAdvisor">
            </div>
            <div class="mb-3">
              <label class="form-label">Members</label>
              <input type="number" class="form-control" id="editOrgMembers">
            </div>
            <div class="mb-3">
              <label class="form-label">Status</label>
              <select class="form-select" id="editOrgStatus">
                <option>Active</option>
                <option>Inactive</option>
              </select>
            </div>
            <div class="mb-3">
              <label class="form-label">Description</label>
              <textarea class="form-control" rows="3" id="editOrgDescription"></textarea>
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

  {{-- Script Section --}}
  @section('page-script')
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        const viewModal = new bootstrap.Modal(document.getElementById("orgDetailsModal"));
        const editModal = new bootstrap.Modal(document.getElementById("editOrgModal"));

        // View
        document.querySelectorAll(".view-details-btn").forEach(btn => {
          btn.addEventListener("click", function () {
            document.getElementById("orgName").textContent = this.dataset.name;
            document.getElementById("orgType").textContent = this.dataset.type;
            document.getElementById("orgDescription").textContent = this.dataset.description;
            document.getElementById("orgMembers").textContent = this.dataset.members;
            document.getElementById("orgAdvisor").textContent = this.dataset.advisor;

            const badge = document.getElementById("orgStatus");
            badge.textContent = this.dataset.status;
            badge.className = "badge " + (this.dataset.status === "Active" ? "bg-label-success" : "bg-label-secondary");

            viewModal.show();
          });
        });

        // Edit
        document.querySelectorAll(".edit-org-btn").forEach(btn => {
          btn.addEventListener("click", function () {
            document.getElementById("editOrgId").value = this.dataset.id;
            document.getElementById("editOrgName").value = this.dataset.name;
            document.getElementById("editOrgType").value = this.dataset.type;
            document.getElementById("editOrgAdvisor").value = this.dataset.advisor;
            document.getElementById("editOrgMembers").value = this.dataset.members;
            document.getElementById("editOrgStatus").value = this.dataset.status;
            document.getElementById("editOrgDescription").value = this.dataset.description;

            editModal.show();
          });
        });
      });
    </script>
  @endsection

@endsection
