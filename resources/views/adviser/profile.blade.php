{{-- resources/views/adviser/profile.blade.php --}}
@extends('layouts.adviserLayout')
@section('title', 'My Profile - Faculty Adviser')

@section('vendor-style')
<link rel="stylesheet" href="{{ asset('assets/vendor/libs/apex-charts/apex-charts.css') }}">
<style>
    #signature-pad {
        border: 2px dashed #ccc;
        border-radius: 12px;
        background: #fff;
        cursor: crosshair;
    }
    .btn-remove-bg {
        background: linear-gradient(45deg, #ff6b6b, #feca57);
        border: none;
        color: white;
    }
    .hover-shadow {
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}
.hover-shadow:hover {
    box-shadow: 0 10px 25px rgba(0,0,0,0.12);
    transform: translateY(-2px);
}
.transition-all {
    transition: all 0.3s ease;
}
</style>
@endsection

@section('page-script')
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

@if(session('success'))
<script>
    Swal.fire({ icon: 'success', title: 'Success!', text: @json(session('success')), timer: 2500, showConfirmButton: false });
</script>
@endif

@if(session('error'))
<script>
    Swal.fire({ icon: 'error', title: 'Error', text: @json(session('error')), timer: 3000, showConfirmButton: false });
</script>
@endif

<script>
document.addEventListener('DOMContentLoaded', function () {
    const canvas = document.getElementById('signature-pad');
    const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255,255,255,0)',
        penColor: 'rgb(0, 0, 0)'
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

    document.getElementById('clear-signature').onclick = () => signaturePad.clear();

    document.getElementById('save-draw').onclick = function () {
        if (signaturePad.isEmpty()) {
            return Swal.fire('Oops!', 'Please draw your signature first.', 'warning');
        }
        uploadSignature(signaturePad.toDataURL('image/png'));
    };

    let currentImageData = null;

    document.getElementById('upload-signature').onchange = function (e) {
        const file = e.target.files[0];
        if (!file) return;

        const reader = new FileReader();
        reader.onload = function (ev) {
            currentImageData = ev.target.result;
            document.getElementById('remove-bg-btn').style.display = 'block';
            document.getElementById('save-original-btn').style.display = 'block';

            Swal.fire({
                title: 'Image Uploaded!',
                imageUrl: currentImageData,
                imageAlt: 'Your signature',
                imageWidth: 320,
                text: 'Remove background for best results (recommended)',
                showCancelButton: true,
                confirmButtonText: 'Remove Background',
                cancelButtonText: 'Keep Original',
                confirmButtonColor: '#ff6b6b'
            }).then((result) => {
                if (result.isConfirmed) {
                    removeBackgroundAndSave();
                } else {
                    uploadSignature(currentImageData);
                }
            });
        };
        reader.readAsDataURL(file);
    };

    function removeBackgroundAndSave() {
        if (!currentImageData) return;

        Swal.fire({
            title: 'Removing background...',
            text: 'Making your signature transparent and clean',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        const img = new Image();
        img.onload = function () {
            const canvas = document.createElement('canvas');
            canvas.width = img.width;
            canvas.height = img.height;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);

            canvas.toBlob(async (blob) => {
                const formData = new FormData();
                formData.append('image_file', blob, 'signature.png');

                try {
                    const response = await fetch('https://clipdrop-api.co/cleanup/v1', {
                        method: 'POST',
                        headers: { 'x-api-key': '' },
                        body: formData
                    });

                    if (!response.ok) throw new Error();

                    const cleanBlob = await response.blob();
                    const cleanUrl = URL.createObjectURL(cleanBlob);
                    uploadSignature(cleanUrl);
                    Swal.fire('Perfect!', 'Background removed!', 'success');
                } catch (err) {
                    console.warn('Clipdrop failed, using original');
                    uploadSignature(currentImageData);
                    Swal.fire('Note', 'Background removal unavailable. Using original.', 'info');
                }
            }, 'image/png');
        };
        img.src = currentImageData;
    }

    document.getElementById('remove-bg-btn').onclick = removeBackgroundAndSave;
    document.getElementById('save-original-btn').onclick = () => uploadSignature(currentImageData);

    function uploadSignature(dataUrl) {
        const formData = new FormData();
        formData.append('signature_data', dataUrl);
        formData.append('_token', '{{ csrf_token() }}');

        fetch('{{ route('adviser.uploadSignature') }}', {
            method: 'POST',
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire('Success!', 'Your signature has been saved.', 'success')
                    .then(() => location.reload());
            } else {
                Swal.fire('Error', data.message || 'Upload failed', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Failed to save signature. Try again.', 'error');
        });
    }
});
</script>
@endsection

@section('content')
<div class="row g-5">

    <!-- LEFT: Signature Manager -->
    <div class="col-lg-4">
        <div class="card h-100">
            <div class="card-body text-center">
                <h5 class="card-title mb-4">Your Signature</h5>

                @if(Auth::user()->signature)
                    <div class="mb-4 p-4 bg-light rounded-3 shadow-sm">
                        <img src="{{ asset('storage/' . Auth::user()->signature) }}"
                             alt="Current Signature" class="img-fluid rounded" style="max-height: 140px;">
                        <p class="text-success small mt-2 mb-0">Currently Active</p>
                    </div>
                @else
                    <div class="alert alert-info small mb-4">
                        No signature yet. Add one to sign permits automatically.
                    </div>
                @endif

                <!-- Draw Signature -->
                <div class="mb-5">
                    <h6 class="text-primary fw-bold mb-3">Draw Your Signature</h6>
                    <canvas id="signature-pad" class="w-100 shadow-sm" height="200"></canvas>
                    <div class="d-flex justify-content-center gap-3 mt-3">
                        <button id="clear-signature" class="btn btn-outline-secondary btn-sm">Clear</button>
                        <button id="save-draw" class="btn btn-primary btn-sm">Save Drawn</button>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Upload Signature -->
                <div>
                    <h6 class="text-primary fw-bold mb-3">OR Upload Image</h6>
                    <input type="file" id="upload-signature" accept="image/*" class="form-control mb-3">

                    <button id="remove-bg-btn" class="btn btn-remove-bg w-100" style="display:none;">
                        Remove Background & Save
                    </button>
                    <button id="save-original-btn" class="btn btn-outline-secondary w-100 mt-2" style="display:none;">
                        Save Original Image
                    </button>
                </div>

                <!-- Remove Signature -->
                @if(Auth::user()->signature)
                    <hr class="my-4">
                    <form action="{{ route('adviser.removeSignature') }}" method="POST" onsubmit="return confirm('Remove your signature permanently?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 btn-sm">
                            Remove Signature
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- RIGHT: Profile Info -->
    <div class="col-lg-8">

                <!-- Advised Organizations (Supports Multiple) -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Advised Organization(s)</h5>
                <span class="badge bg-light text-primary">
                    {{ Auth::user()->advisedOrganizations->count() }} Active
                </span>
            </div>
            <div class="card-body">
                @forelse(Auth::user()->advisedOrganizations as $org)
                    <div class="border rounded-3 p-4 mb-3 hover-shadow transition-all" style="transition: all 0.2s;">
                        <div class="d-flex align-items-start gap-4">
                            <!-- Organization Logo -->


                            <!-- Organization Details -->
                            <div class="flex-grow-1">
                                <h5 class="mb-1 text-primary fw-bold">{{ $org->organization_name }}</h5>
                                <div class="d-flex flex-wrap gap-3 text-sm text-muted mb-2">
                                    <span><strong>Type:</strong> {{ $org->organization_type ?? 'Not specified' }}</span>
                                    @if($org->status)
                                        <span>• <span class="badge bg-{{ $org->status === 'active' ? 'success' : 'warning' }} text-white">
                                            {{ ucfirst($org->status) }}
                                        </span></span>
                                    @endif
                                </div>

                                @if($org->description)
                                    <p class="text-muted small mb-0 mt-2">
                                        {{ Str::limit($org->description, 150) }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="alert alert-warning mb-0 text-center py-4">
                        <i class="ti ti-alert-circle me-2"></i>
                        You are not currently assigned as Faculty Adviser to any organization.
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Account Details -->
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title mb-3">Account Details</h5>
                <div class="row g-4">
                    <div class="col-md-6"><strong>Username</strong><p>{{ Auth::user()->username }}</p></div>
                    <div class="col-md-6"><strong>Role</strong><p>Faculty Adviser</p></div>
                    <div class="col-12"><strong>Member Since</strong><p>{{ Auth::user()->created_at->format('F d, Y') }}</p></div>
                </div>
            </div>
        </div>

        <!-- Personal Information -->
        <div class="card">
            <div class="card-body">
                <h5 class="card-title mb-3">Personal Information</h5>
                @php $p = Auth::user()->user_profile; @endphp
                <div class="row g-4">
                    <div class="col-md-8">
                        <strong>Full Name</strong>
                        <p class="fw-bold">
                            {{ trim($p?->first_name . ' ' . ($p?->middle_name ? substr($p->middle_name,0,1).'.' : '') . ' ' . $p?->last_name . ' ' . ($p?->suffix ?? '')) }}
                        </p>
                    </div>
                    <div class="col-md-4"><strong>Sex</strong><p>{{ $p?->sex ?? 'Not set' }}</p></div>
                    <div class="col-12"><strong>Address</strong><p class="text-muted">{{ $p?->address ?? 'Not provided' }}</p></div>
                </div>

                <hr class="my-4">
                <h6>Contact Information</h6>
                <div class="row g-3 mt-3">
                    <div class="col-md-6">
                        <input type="email" class="form-control" value="{{ $p?->email }}" disabled placeholder="Email">
                    </div>
                    <div class="col-md-6">
                        <input type="text" class="form-control" value="{{ $p?->contact_number }}" disabled placeholder="Contact Number">
                    </div>
                </div>
                <small class="text-muted">Contact your admin to update personal info.</small>
            </div>
        </div>
    </div>
</div>
@endsection
