{{-- resources/views/student/profile.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'My Profile')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
<style>
    #signature-pad {
        border: 2px dashed #d9dee3;
        border-radius: 0.5rem;
        background: #fff;
        cursor: crosshair;
        transition: border-color 0.3s;
    }

    #signature-pad:hover {
        border-color: #696cff;
    }

    .signature-preview {
        min-height: 150px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 0.5rem;
    }

    .card {
        box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
        border: none;
        border-radius: 0.5rem;
    }

    .info-label {
        font-size: 0.75rem;
        font-weight: 600;
        text-transform: uppercase;
        color: #a1acb8;
        margin-bottom: 0.25rem;
    }

    .info-value {
        color: #566a7f;
        font-weight: 500;
    }
</style>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({
        icon: 'success',
        title: 'Success!',
        text: @json(session('success')),
        confirmButtonColor: '#71dd37',
        timer: 2500,
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
        confirmButtonColor: '#ff3e1d',
        timer: 3000,
        showConfirmButton: false
    });
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: 'rgb(0, 0, 0)',
        minWidth: 1,
        maxWidth: 2.5
    });

    function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
    }
    window.addEventListener('resize', resizeCanvas);
    resizeCanvas();

    // Clear canvas
    document.getElementById('clear-signature').onclick = () => {
        signaturePad.clear();
        Swal.fire({
            icon: 'info',
            title: 'Canvas Cleared',
            text: 'Draw your signature again',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    };

    // Save drawn signature
    document.getElementById('save-draw').onclick = function () {
        if (signaturePad.isEmpty()) {
            return Swal.fire({
                icon: 'warning',
                title: 'Empty Signature',
                text: 'Please draw your signature first.',
                confirmButtonColor: '#696cff'
            });
        }

        Swal.fire({
            title: 'Save Signature?',
            text: "This will replace your current signature",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#696cff',
            cancelButtonColor: '#8592a3',
            confirmButtonText: 'Yes, save it!'
        }).then((result) => {
            if (result.isConfirmed) {
                uploadSignature(signaturePad.toDataURL('image/png'));
            }
        });
    };

    // File upload with modal
    document.getElementById('upload-signature').onchange = function (e) {
        const file = e.target.files[0];
        if (!file) return;

        // Validate file type
        if (!file.type.startsWith('image/')) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid File',
                text: 'Please upload an image file',
                confirmButtonColor: '#ff3e1d'
            });
            this.value = '';
            return;
        }

        // Validate file size (max 2MB)
        if (file.size > 2 * 1024 * 1024) {
            Swal.fire({
                icon: 'error',
                title: 'File Too Large',
                text: 'Please upload an image smaller than 2MB',
                confirmButtonColor: '#ff3e1d'
            });
            this.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = function (ev) {
            const imageData = ev.target.result;

            Swal.fire({
                title: 'Confirm Signature Upload',
                html: `
                    <div class="text-center">
                        <img src="${imageData}" alt="Preview" style="max-width: 100%; max-height: 200px; border-radius: 0.5rem; margin: 1rem 0;">
                        <p class="text-muted small">This will replace your current signature</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonColor: '#696cff',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Upload Signature',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    uploadSignature(imageData);
                }
                // Reset file input
                document.getElementById('upload-signature').value = '';
            });
        };
        reader.readAsDataURL(file);
    };

    // Universal upload function
    function uploadSignature(dataUrl) {
        Swal.fire({
            title: 'Uploading...',
            text: 'Please wait while we save your signature',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading()
        });

        const formData = new FormData();
        formData.append('signature_data', dataUrl);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('bargo.uploadSignature') }}', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: 'Signature saved successfully!',
                    confirmButtonColor: '#71dd37'
                }).then(() => location.reload());
            } else {
                throw new Error(data.message || 'Failed');
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Upload Failed',
                text: 'Please try again later.',
                confirmButtonColor: '#ff3e1d'
            });
        });
    }

    // Remove signature confirmation
    const removeForm = document.getElementById('remove-signature-form');
    if (removeForm) {
        removeForm.onsubmit = function(e) {
            e.preventDefault();

            Swal.fire({
                title: 'Remove Signature?',
                text: "You won't be able to generate permits without a signature",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ff3e1d',
                cancelButtonColor: '#8592a3',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    this.submit();
                }
            });
        };
    }
});
</script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <h4 class="fw-semibold mb-1">My Profile</h4>
            <p class="text-muted small mb-0">Manage your account information and signature</p>
        </div>
    </div>

    <div class="row g-4">

        <!-- LEFT: Signature Manager -->
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-draw text-primary me-2"></i>
                        Digital Signature
                    </h5>
                </div>
                <div class="card-body">

                    <!-- Current Signature Display -->
                    <div class="mb-4">
                        <label class="info-label">Current Signature</label>
                        <div class="signature-preview border p-3">
                            @if(Auth::user()->signature)
                                <img src="{{ asset('storage/' . Auth::user()->signature) }}"
                                     alt="Current Signature"
                                     class="img-fluid"
                                     style="max-height: 120px; max-width: 100%;">
                            @else
                                <div class="text-center text-muted">
                                    <i class="mdi mdi-signature-freehand mdi-48px mb-2 opacity-50"></i>
                                    <p class="small mb-0">No signature yet</p>
                                </div>
                            @endif
                        </div>
                        @if(Auth::user()->signature)
                            <small class="text-success d-flex align-items-center mt-2">
                                <i class="mdi mdi-check-circle me-1"></i>
                                Signature Active
                            </small>
                        @else
                            <small class="text-warning d-flex align-items-center mt-2">
                                <i class="mdi mdi-alert me-1"></i>
                                Add signature to generate permits
                            </small>
                        @endif
                    </div>

                    <hr class="my-4">

                    <!-- Draw Signature -->
                    <div class="mb-4">
                        <label class="info-label">
                            <i class="mdi mdi-pencil me-1"></i>
                            Draw Signature
                        </label>
                        <canvas id="signature-pad" class="w-100" height="180"></canvas>
                        <div class="d-flex gap-2 mt-3">
                            <button id="clear-signature" class="btn btn-label-secondary btn-sm flex-fill">
                                <i class="mdi mdi-eraser me-1"></i>Clear
                            </button>
                            <button id="save-draw" class="btn btn-primary btn-sm flex-fill">
                                <i class="mdi mdi-content-save me-1"></i>Save
                            </button>
                        </div>
                    </div>

                    <div class="text-center my-3">
                        <span class="badge bg-label-secondary">OR</span>
                    </div>

                    <!-- Upload Signature -->
                    <div class="mb-4">
                        <label class="info-label">
                            <i class="mdi mdi-upload me-1"></i>
                            Upload Image
                        </label>
                        <input type="file"
                               id="upload-signature"
                               accept="image/png,image/jpeg,image/jpg"
                               class="form-control">
                        <small class="text-muted d-block mt-2">
                            <i class="mdi mdi-information-outline me-1"></i>
                            Max 2MB • PNG, JPG formats only
                        </small>
                    </div>

                    <!-- Remove Signature -->
                    @if(Auth::user()->signature)
                        <hr class="my-4">
                        <form id="remove-signature-form"
                              action="{{ route('bargo.removeSignature') }}"
                              method="POST">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-label-danger w-100">
                                <i class="mdi mdi-delete-outline me-1"></i>
                                Remove Signature
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- RIGHT: Profile Info -->
        <div class="col-lg-8">



            <!-- Account Details -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-account-circle text-primary me-2"></i>
                        Account Details
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="info-label">Username</div>
                            <div class="info-value">{{ Auth::user()->username }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Role</div>
                            <div class="info-value">{{ ucfirst(str_replace('_', ' ', Auth::user()->account_role)) }}</div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Account Status</div>
                            <span class="badge bg-label-success">Active</span>
                        </div>
                        <div class="col-md-6">
                            <div class="info-label">Member Since</div>
                            <div class="info-value">{{ Auth::user()->created_at->format('F d, Y') }}</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="mdi mdi-account-details text-primary me-2"></i>
                        Personal Information
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $p = Auth::user()->user_profile;
                        $fullName = trim(
                            ($p?->first_name ?? '') . ' ' .
                            ($p?->middle_name ? substr($p->middle_name,0,1).'.' : '') . ' ' .
                            ($p?->last_name ?? '') . ' ' .
                            ($p?->suffix ?? '')
                        );
                    @endphp

                    <div class="row g-4">
                        <div class="col-md-8">
                            <div class="info-label">Full Name</div>
                            <div class="info-value">{{ $fullName }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Sex</div>
                            <div class="info-value">{{ $p?->sex ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-2">
                            <div class="info-label">Type</div>
                            <div class="info-value">{{ ucfirst($p?->type ?? 'N/A') }}</div>
                        </div>
                        <div class="col-12">
                            <div class="info-label">Address</div>
                            <div class="info-value text-muted">{{ $p?->address ?? 'Not set' }}</div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <div class="row g-4">
                        <div class="col-12">
                            <div class="info-label">Contact Information</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Email Address</label>
                            <input type="email" class="form-control" value="{{ $p?->email }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small text-muted">Contact Number</label>
                            <input type="text" class="form-control" value="{{ $p?->contact_number }}" disabled>
                        </div>
                        <div class="col-12">
                            <div class="alert alert-info d-flex align-items-center mb-0" role="alert">
                                <i class="mdi mdi-information-outline me-2"></i>
                                <small>Contact your admin to update personal information.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
