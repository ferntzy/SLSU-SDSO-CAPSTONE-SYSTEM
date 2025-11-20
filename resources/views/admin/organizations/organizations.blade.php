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
    @include("admin.organizations.orgjs")
</head>
@endsection

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
                        <td>{{ $org->advisor_name ?? 'N/A' }}</td>
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
            <form id="addOrgForm" action="{{ route('organizations.store') }}" method="POST">
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
                        <select name="user_id" class="form-select" required>
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

                    {{-- Officer Fields --}}
                    <hr>
                    <h6 class="fw-semibold">Organization Officer</h6>
                    <div class="mb-3">
                        <label class="form-label">Officer</label>
                        <select name="officer_id" class="form-select">
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
                        <input type="email" name="contact_email" class="form-control">
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control">
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

    // Optional: Add your JS for view/edit/delete buttons here
});
</script>
@endsection
@endsection
