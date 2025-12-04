@php $container = 'container-xxl'; @endphp
@extends('layouts.adviserLayout')
@section('title', 'Pending Permit Reviews')

@section('content')
<div class="{{ $container }} py-4">

  {{-- Header Section --}}
  <div class="d-flex justify-content-between align-items-center mb-4">
    <div>
      <h4 class="fw-semibold mb-1" style="color: #566a7f;">Pending Permit Reviews</h4>
      <p class="text-muted small mb-0">Review and approve organization permit requests</p>
    </div>
    <span class="badge bg-label-primary px-3 py-2" style="font-size: 0.875rem;">
      {{ count($pendingPermits) }} Pending
    </span>
  </div>

  {{-- Approvals List --}}
  <div class="card">
    <div class="table-responsive">
      <table class="table table-hover mb-0">
        <thead class="table-light">
          <tr>
            <th class="text-nowrap" style="font-weight: 500; color: #566a7f;">Activity</th>
            <th class="text-nowrap" style="font-weight: 500; color: #566a7f;">Organization</th>
            <th class="text-nowrap" style="font-weight: 500; color: #566a7f;">Date</th>
            <th class="text-nowrap" style="font-weight: 500; color: #566a7f;">Requested</th>

            <th class="text-nowrap text-center" style="font-weight: 500; color: #566a7f;">Actions</th>
          </tr>
        </thead>
        <tbody>
          @forelse($pendingPermits as $permitFlow)
            @php
              $permit = $permitFlow->permit;
              $stages = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
              $approvals = $permit->eventApprovalFlows ?? collect();
              $approvedCount = $approvals->where('status', 'approved')->count();
            @endphp

            <tr>
              {{-- Activity Title --}}
              <td>
                <div class="d-flex flex-column">
                  <span class="fw-medium text-heading">{{ Str::limit($permit->title_activity, 40) }}</span>
                  <small class="text-muted">{{ Str::limit($permit->purpose, 50) }}</small>
                </div>
              </td>

              {{-- Organization --}}
              <td>
                <span class="text-heading">{{ $permit->organization->organization_name ?? 'Unknown' }}</span>
              </td>

              {{-- Event Date --}}
              <td class="text-nowrap">
                <small>{{ \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') }}</small>
                @if($permit->date_end && $permit->date_start != $permit->date_end)
                  <br><small class="text-muted">to {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}</small>
                @endif
              </td>

              {{-- Requested Date --}}
              <td class="text-nowrap">
                <small class="text-muted">{{ $permitFlow->created_at->format('M d, Y') }}</small>
              </td>

              {{-- Progress Status --}}


              {{-- Actions Dropdown --}}
              <td>
                <div class="d-flex justify-content-center">
                  <div class="dropdown">
                    <button class="btn btn-sm btn-icon btn-text-secondary rounded-pill"
                      type="button" data-bs-toggle="dropdown" aria-expanded="false">
                      <i class="mdi mdi-dots-vertical mdi-20px"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                      @if($permit->pdf_data)
                        <li>
                          <a class="dropdown-item" href="javascript:void(0);"
                            data-bs-toggle="modal" data-bs-target="#pdfModal{{ $permit->hashed_id }}">
                            <i class="mdi mdi-file-pdf-box me-2 text-danger"></i>
                            View PDF
                          </a>
                        </li>
                      @endif
                      <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                          data-bs-toggle="modal" data-bs-target="#detailsModal{{ $permitFlow->approval_id }}">
                          <i class="mdi mdi-information-outline me-2 text-info"></i>
                          View Details
                        </a>
                      </li>
                      <li><hr class="dropdown-divider"></li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                          data-bs-toggle="modal" data-bs-target="#approveModal{{ $permitFlow->approval_id }}">
                          <i class="mdi mdi-check-circle-outline me-2 text-success"></i>
                          Approve
                        </a>
                      </li>
                      <li>
                        <a class="dropdown-item" href="javascript:void(0);"
                          data-bs-toggle="modal" data-bs-target="#rejectModal{{ $permitFlow->approval_id }}">
                          <i class="mdi mdi-close-circle-outline me-2 text-danger"></i>
                          Reject
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
              </td>
            </tr>

          @empty
            <tr>
              <td colspan="6" class="text-center py-5">
                <div class="d-flex flex-column align-items-center">
                  <i class="mdi mdi-clipboard-check-outline text-muted mb-2" style="font-size: 3rem;"></i>
                  <h5 class="text-muted mb-1">No Pending Approvals</h5>
                  <p class="text-muted small">All permits have been reviewed</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>
</div>

{{-- Modals Section --}}
@foreach($pendingPermits as $permitFlow)
  @php
    $permit = $permitFlow->permit;
    $stages = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
    $approvals = $permit->eventApprovalFlows ?? collect();
  @endphp

  {{-- ================== DETAILS MODAL ================== --}}
 {{-- ================== DETAILS MODAL (NOW WITH UPLOADED FILES) ================== --}}
<div class="modal fade" id="detailsModal{{ $permitFlow->approval_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header  text-white">
                <h5 class="modal-title">
                    <i class="mdi mdi-file-document-multiple-outline me-2"></i>
                    Permit Details & Supporting Documents
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <!-- Permit Info -->
                <div class="row g-4 mb-4">
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">ACTIVITY TITLE</label>
                        <p class="fs-5 fw-semibold text-primary">{{ $permit->title_activity }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">ORGANIZATION</label>
                        <p class="fs-5">{{ $permit->organization->organization_name ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">NATURE OF ACTIVITY</label>
                        <p>{{ $permit->nature ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">VENUE</label>
                        <p>{{ $permit->venue ?? 'N/A' }}</p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">EVENT DATE</label>
                        <p>
                            {{ \Carbon\Carbon::parse($permit->date_start)->format('F j, Y') }}
                            @if($permit->date_end && $permit->date_end != $permit->date_start)
                                → {{ \Carbon\Carbon::parse($permit->date_end)->format('F j, Y') }}
                            @endif
                        </p>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold text-muted small">EXPECTED PARTICIPANTS</label>
                        <p>{{ $permit->number ?? 'N/A' }}</p>
                    </div>
                </div>

                <hr class="my-4">

                <!-- Supporting Documents Section -->
                <h6 class="fw-bold text-primary mb-3">
                    <i class="mdi mdi-folder-zip-outline me-2"></i>
                    Supporting Documents ({{ $permit->offCampusRequirements->count() }})
                </h6>

                @if($permit->offCampusRequirements->count() >= 0)
                    <div class="row g-3">
                        @foreach($permit->offCampusRequirements as $file)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 border shadow-sm hover-shadow">
                                    <div class="card-body text-center py-4">
                                        @php
                                            $ext = pathinfo($file->file_path, PATHINFO_EXTENSION);
                                            $isImage = in_array(strtolower($ext), ['jpg','jpeg','png','gif','webp']);
                                        @endphp

                                        @if($isImage)
                                            <img src="{{ Storage::url($file->file_path) }}"
                                                 alt="Document" class="img-fluid rounded mb-3"
                                                 style="max-height: 120px; object-fit: cover;">
                                        @else
                                            <i class="mdi mdi-file-document-outline text-primary" style="font-size: 4rem;"></i>
                                        @endif

                                        <h6 class="mt-3 mb-1 text-truncate" title="{{ basename($file->file_path) }}">
                                            {{ Str::limit(basename($file->file_path), 25) }}
                                        </h6>


                                        <div class="mt-3">
                                            <a href="{{ Storage::url($file->file_path) }}"
                                               target="_blank"
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="mdi mdi-eye"></i> View
                                            </a>
                                            <a href="{{ Storage::url($file->file_path) }}"
                                               download
                                               class="btn btn-sm btn-outline-success">
                                                <i class="mdi mdi-download"></i> Download
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-5 bg-light rounded">
                        <i class="mdi mdi-folder-open-outline text-muted" style="font-size: 4rem;"></i>
                        <p class="mt-3 text-muted">No supporting documents uploaded</p>
                    </div>
                @endif

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    <i class="mdi mdi-close me-1"></i> Close
                </button>
            </div>
        </div>
    </div>
</div>
  {{-- ================== APPROVE MODAL ================== --}}
  <div class="modal fade" id="approveModal{{ $permitFlow->approval_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="approveForm{{ $permitFlow->approval_id }}"
        action="{{ route('adviser.permit.approve', $permitFlow->approval_id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Approve Permit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info d-flex align-items-center" role="alert">
            <i class="mdi mdi-information-outline me-2 mdi-20px"></i>
            <div>You are approving: <strong>{{ $permit->title_activity }}</strong></div>
          </div>

          {{-- Adviser Name --}}
          <div class="mb-3">
            <label class="form-label">Adviser Name</label>
            @php
              $fullName = trim(
                (Auth::user()->profile?->first_name ?? '') . ' ' .
                (Auth::user()->profile?->middle_name ? strtoupper(substr(Auth::user()->profile->middle_name, 0, 1)) . '. ' : '') .
                (Auth::user()->profile?->last_name ?? '') . ' ' .
                (Auth::user()->profile?->suffix ?? '')
              );
            @endphp
            <input type="text" name="adviser_name" class="form-control"
              value="{{ strtoupper($fullName) }}" readonly>
          </div>

          {{-- Display User Signature --}}
          <div class="mb-3">
            <label class="form-label">Your Signature</label>
            <div class="border rounded p-3 bg-lighter text-center" style="min-height: 120px;">
              @php
                $signaturePath = null;
                if (Auth::user()->signature && file_exists(storage_path('app/public/' . Auth::user()->signature))) {
                  $signaturePath = asset('storage/' . Auth::user()->signature);
                }
              @endphp

              @if($signaturePath)
                <img src="{{ $signaturePath }}" alt="Signature" style="max-height: 100px; max-width: 100%;">
              @else
                <div class="text-muted d-flex flex-column align-items-center justify-content-center" style="height: 100px;">
                  <i class="mdi mdi-draw mdi-36px mb-2"></i>
                  <small>No signature on file</small>
                  <small class="text-warning">Please update your profile</small>
                </div>
              @endif
            </div>
          </div>

          <input type="hidden" name="signature_data" value="">
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-success" onclick="confirmApprove('{{ $permitFlow->approval_id }}', event)" data-bs-dismiss="modal">
            <i class="mdi mdi-check me-1"></i>Confirm Approval
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ================== REJECT MODAL ================== --}}
  <div class="modal fade" id="rejectModal{{ $permitFlow->approval_id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <form class="modal-content" id="rejectForm{{ $permitFlow->approval_id }}"
        action="{{ route('adviser.permit.reject', $permitFlow->approval_id) }}" method="POST">
        @csrf
        <div class="modal-header">
          <h5 class="modal-title">Reject Permit</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="mdi mdi-alert-outline me-2 mdi-20px"></i>
            <div>You are rejecting: <strong>{{ $permit->title_activity }}</strong></div>
          </div>

          <div class="mb-3">
            <label class="form-label">Reason for Rejection <span class="text-danger">*</span></label>
            <textarea class="form-control" name="comments" rows="4" required
              placeholder="Please provide a detailed reason for rejection..."></textarea>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" class="btn btn-danger" onclick="confirmReject('{{ $permitFlow->approval_id }}')">
            <i class="mdi mdi-close me-1"></i>Confirm Rejection
          </button>
        </div>
      </form>
    </div>
  </div>

  {{-- ================== PDF MODAL ================== --}}
  @if($permit->pdf_data)
    <div class="modal fade" id="pdfModal{{ $permit->hashed_id }}" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content" style="height: 90vh;">
          <div class="modal-header">
            <h5 class="modal-title">Permit PDF Viewer</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body p-0">
            <iframe src="{{ route('adviser.permit.pdf', ['hashed_id' => $permit->hashed_id]) }}"
              style="width:100%; height:100%; border:none;"></iframe>
          </div>
        </div>
      </div>
    </div>
  @endif

@endforeach

{{-- SweetAlert Success/Error Messages --}}
@if(session('success'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'success',
      title: 'Success!',
      text: '{{ session("success") }}',
      confirmButtonColor: '#71dd37',
      confirmButtonText: 'OK'
    });
  });
</script>
@endif

@if(session('error'))
<script>
  document.addEventListener('DOMContentLoaded', function() {
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: '{{ session("error") }}',
      confirmButtonColor: '#ff3e1d',
      confirmButtonText: 'OK'
    });
  });
</script>
@endif

{{-- Confirmation Scripts --}}
<script>
function confirmApprove(id, event) {
  event.preventDefault();

  const form = document.getElementById('approveForm' + id);
  const modalElement = document.getElementById('approveModal' + id);

  // Close the modal immediately
  const modalInstance = bootstrap.Modal.getInstance(modalElement);
  if (modalInstance) {
    modalInstance.hide();
  }

  // Small delay to ensure modal is fully closed before showing SweetAlert
  setTimeout(() => {
    Swal.fire({
      title: 'Approve Permit?',
      text: "This action will approve the permit request",
      icon: 'question',
      showCancelButton: true,
      confirmButtonColor: '#71dd37',
      cancelButtonColor: '#8592a3',
      confirmButtonText: 'Yes, approve it!',
      cancelButtonText: 'Cancel'
    }).then((result) => {
      if (result.isConfirmed) {
        // Show loading
        Swal.fire({
          title: 'Processing...',
          text: 'Please wait while we process your approval',
          allowOutsideClick: false,
          allowEscapeKey: false,
          didOpen: () => {
            Swal.showLoading();
          }
        });
        form.submit();
      } else if (result.isDismissed) {
        // If user cancels, show the modal again
        const newModalInstance = new bootstrap.Modal(modalElement);
        newModalInstance.show();
      }
    });
  }, 300);
}

function confirmReject(id) {
  const form = document.getElementById('rejectForm' + id);
  const comments = form.querySelector('textarea[name="comments"]').value;

  if (!comments || comments.trim().length < 10) {
    Swal.fire({
      icon: 'warning',
      title: 'Reason Required',
      text: 'Please provide a detailed reason for rejection (at least 10 characters)',
      confirmButtonColor: '#696cff'
    });
    return;
  }

  Swal.fire({
    title: 'Reject Permit?',
    text: "This action will reject the permit request",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#ff3e1d',
    cancelButtonColor: '#8592a3',
    confirmButtonText: 'Yes, reject it!',
    cancelButtonText: 'Cancel'
  }).then((result) => {
    if (result.isConfirmed) {
      // Show loading
      Swal.fire({
        title: 'Processing...',
        text: 'Please wait while we process your rejection',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
          Swal.showLoading();
        }
      });
      form.submit();
    }
  });
}
</script>

<style>
/* Materio-Pro Inspired Styling */
.table-hover tbody tr:hover {
  background-color: rgba(105, 108, 255, 0.04);
}

.btn-icon {
  width: 32px;
  height: 32px;
  padding: 0;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.bg-lighter {
  background-color: #f8f9fa;
}

.text-heading {
  color: #566a7f;
}

.badge.bg-label-primary {
  background-color: rgba(105, 108, 255, 0.16) !important;
  color: #696cff !important;
}

.badge.bg-label-info {
  background-color: rgba(3, 195, 236, 0.16) !important;
  color: #03c3ec !important;
}

.badge.bg-label-success {
  background-color: rgba(113, 221, 55, 0.16) !important;
  color: #71dd37 !important;
}

.badge.bg-label-danger {
  background-color: rgba(255, 62, 29, 0.16) !important;
  color: #ff3e1d !important;
}

.badge.bg-label-secondary {
  background-color: rgba(133, 146, 163, 0.16) !important;
  color: #8592a3 !important;
}

.card {
  box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12);
  border: none;
  border-radius: 0.5rem;
}

.modal-content {
  border-radius: 0.5rem;
}

.dropdown-menu {
  box-shadow: 0 4px 16px rgba(67, 89, 113, 0.15);
  border: none;
  border-radius: 0.5rem;
}

.dropdown-item {
  padding: 0.5rem 1rem;
  transition: all 0.2s;
}

.dropdown-item:hover {
  background-color: rgba(105, 108, 255, 0.08);
}

.dropdown-item i {
  font-size: 1.25rem;
}
</style>
@endsection
