@php $container = 'container-xxl'; @endphp
@extends('layouts/contentNavbarLayout')
@section('title', 'Pending Permit Reviews')

@section('content')
  <div class="{{ $container }} py-4">

    <h3 class="fw-bold mb-4 text-primary">Pending Permit Reviews</h3>

    <div class="row g-4">

      @forelse($pendingPermits as $permitFlow)
        @php
          $permit = $permitFlow->permit;
          $stages = ['Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
          $approvals = $permit->eventApprovalFlows ?? collect();
        @endphp

        <div class="col-12 col-md-6 col-xl-4">
          <div class="card shadow-sm border-0 h-100" style="border-radius: 14px;">

            {{-- Header --}}
            <div class="card-header bg-primary text-white py-3" style="border-radius: 14px 14px 0 0;">
              <h5 class="mb-0 fw-semibold">{{ $permit->title_activity }}</h5>
              <small class="badge bg-light text-dark mt-2">
                Requested: {{ $permitFlow->created_at->format('M d, Y') }}
              </small>
            </div>

            {{-- Body --}}
            <div class="card-body mt-2" style="font-size: 15px;">
              <p class="text-muted mb-1">
                <strong>Organization:</strong> {{ $permit->organization->organization_name ?? 'Unknown' }}
              </p>

              <p class="mb-1"><strong>Purpose:</strong> {{ Str::limit($permit->purpose, 100) }}</p>

              <p class="mb-1"><strong>Venue:</strong> {{ $permit->venue ?? 'N/A' }}</p>

              <p class="mb-3">
                <strong>Date:</strong>
                {{ \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') }}
                @if($permit->date_end)
                  - {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}
                @endif
              </p>

              {{-- Action Buttons --}}
              <div class="d-flex gap-2 flex-wrap">

                {{-- View PDF --}}
                @if($permit->pdf_data)
                  <button class="btn btn-info btn-sm px-3" data-bs-toggle="modal"
                    data-bs-target="#pdfModal_{{ $permit->hashed_id }}">
                    View PDF
                  </button>
                @else
                  <span class="text-muted small">No PDF uploaded</span>
                @endif

                {{-- Approve Button --}}
                <button class="btn btn-success btn-sm px-3" data-bs-toggle="modal"
                  data-bs-target="#approveModal_{{ $permitFlow->approval_id }}">
                  Approve
                </button>

                {{-- Reject Button --}}
                <button class="btn btn-danger btn-sm px-3" data-bs-toggle="modal"
                  data-bs-target="#rejectModal_{{ $permitFlow->approval_id }}">
                  Reject
                </button>

              </div>

              {{-- Progress Row --}}
              <div class="mt-4">
                <small class="text-muted">Approval Progress</small>
                <div class="d-flex flex-wrap gap-2 mt-2">
                  @foreach($stages as $stage)
                    @php
                      $ap = $approvals->firstWhere('approver_role', $stage);
                      $status = $ap->status ?? 'pending';
                      $badgeColor = $status === 'approved' ? 'success' : ($status === 'rejected' ? 'danger' : 'secondary');
                    @endphp
                    <span class="badge bg-{{ $badgeColor }} px-3 py-2">
                      {{ str_replace('_', ' ', $stage) }}
                    </span>
                  @endforeach
                </div>
              </div>
            </div>
          </div>
        </div>

        {{-- REJECT MODAL --}}
        <div class="modal fade" id="rejectModal_{{ $permitFlow->approval_id }}" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered">
            <form class="modal-content" action="{{ route('faculty.reject', $permitFlow->approval_id) }}" method="POST">
              @csrf
              <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Reject Permit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">
                <label class="form-label">Reason / Comments</label>
                <textarea class="form-control" name="comments" rows="4" required></textarea>
              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-danger">Confirm Reject</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        {{-- APPROVE MODAL --}}
        <div class="modal fade" id="approveModal_{{ $permitFlow->approval_id }}" tabindex="-1">
          <div class="modal-dialog modal-dialog-centered modal-lg">
            <form class="modal-content" action="{{ route('faculty.approve', $permitFlow->approval_id) }}" method="POST"
              enctype="multipart/form-data">
              @csrf
              <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Approve Permit</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body">

                <p class="fw-semibold">You are approving: {{ $permit->title_activity }}</p>

                <div class="row g-3">
                  <div class="col-12">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="password" class="form-control" required>
                  </div>

                  {{-- Adviser Name --}}
                  <div class="col-12">
                    <label class="form-label">Adviser Name</label>
                    <input type="text" name="adviser_name" class="form-control"
                      id="adviserName_{{ $permitFlow->approval_id }}"
                      value="{{ strtoupper(Auth::user()->profile->full_name ?? '') }}">
                  </div>

                  {{-- Signature Canvas --}}
                  <div class="col-12">
                    <label class="form-label">Signature (draw or upload)</label>
                    <canvas id="signatureCanvas_{{ $permitFlow->approval_id }}" width="550" height="160"
                      class="border rounded w-100" style="cursor: crosshair;"></canvas>
                    <button type="button" class="btn btn-outline-secondary btn-sm mt-2"
                      onclick="clearSignature('{{ $permitFlow->approval_id }}')">Clear Signature</button>
                    <input type="hidden" name="signature_data" id="signatureData_{{ $permitFlow->approval_id }}">
                    <small class="text-muted d-block mt-2">Or upload signature</small>
                    <input type="file" name="signature_upload" class="form-control" accept="image/*">
                  </div>

                  {{-- Editable Coordinates --}}
                  <div class="col-6">
                    <label class="form-label">Signature X</label>
                    <input type="number" name="signature_x" class="form-control" value="153">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Signature Y</label>
                    <input type="number" name="signature_y" class="form-control" value="207">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Name X</label>
                    <input type="number" name="name_x" class="form-control" value="153">
                  </div>
                  <div class="col-6">
                    <label class="form-label">Name Y</label>
                    <input type="number" name="name_y" class="form-control" value="223">
                  </div>

                </div>

              </div>
              <div class="modal-footer">
                <button type="submit" class="btn btn-success px-4">Confirm Approve</button>
                <button type="button" class="btn btn-secondary px-4" data-bs-dismiss="modal">Cancel</button>
              </div>
            </form>
          </div>
        </div>

        {{-- PDF VIEW MODAL --}}
        @if($permit->pdf_data)
          <div class="modal fade" id="pdfModal_{{ $permit->hashed_id }}" tabindex="-1">
            <div class="modal-dialog modal-xl modal-dialog-centered">
              <div class="modal-content" style="height: 90vh;">
                <div class="modal-header bg-primary text-white">
                  <h5 class="modal-title">Permit PDF Viewer</h5>
                  <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                  <iframe src="{{ route('faculty.permit.view', ['hashed_id' => $permit->hashed_id]) }}"
                    style="width:100%; height:100%; border:none;"></iframe>
                </div>
              </div>
            </div>
          </div>
        @endif

      @empty
        <div class="text-center py-5">
          <h5 class="text-muted">No pending approvals.</h5>
        </div>
      @endforelse
    </div>

  </div>
@endsection
@if(session('success'))
  <script>
    Swal.fire({
      icon: 'success',
      title: 'Success',
      text: '{{ session("success") }}'
    });
  </script>
@endif

@if(session('error'))
  <script>
    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: '{{ session("error") }}'
    });
  </script>
@endif

<script>
  document.addEventListener("DOMContentLoaded", function () {
    @foreach($pendingPermits as $permitFlow)
      setupCanvas('{{ $permitFlow->approval_id }}');
    @endforeach
  });

  function setupCanvas(id) {
    const canvas = document.getElementById('signatureCanvas_' + id);
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    let drawing = false;

    function getPosition(e) {
      const rect = canvas.getBoundingClientRect();
      let x, y;

      if (e.touches) {
        x = e.touches[0].clientX - rect.left;
        y = e.touches[0].clientY - rect.top;
      } else {
        x = e.clientX - rect.left;
        y = e.clientY - rect.top;
      }

      // Adjust for canvas CSS scaling
      x = x * (canvas.width / rect.width);
      y = y * (canvas.height / rect.height);

      return { x, y };
    }

    canvas.addEventListener('mousedown', e => { drawing = true; draw(e); });
    canvas.addEventListener('mouseup', () => { drawing = false; ctx.beginPath(); saveSignature(id) });
    canvas.addEventListener('mousemove', draw);

    canvas.addEventListener('touchstart', e => { drawing = true; draw(e); });
    canvas.addEventListener('touchend', () => { drawing = false; ctx.beginPath(); saveSignature(id) });
    canvas.addEventListener('touchmove', draw);

    function draw(e) {
      if (!drawing) return;
      e.preventDefault();
      const { x, y } = getPosition(e);

      ctx.lineWidth = 2;
      ctx.lineCap = 'round';
      ctx.strokeStyle = '#000';

      ctx.lineTo(x, y);
      ctx.stroke();
      ctx.beginPath();
      ctx.moveTo(x, y);
    }
  }


  function saveSignature(id) {
    document.getElementById('signatureData_' + id).value =
      document.getElementById('signatureCanvas_' + id).toDataURL("image/png");
  }

  function clearSignature(id) {
    const canvas = document.getElementById('signatureCanvas_' + id);
    canvas.getContext('2d').clearRect(0, 0, canvas.width, canvas.height);
    document.getElementById('signatureData_' + id).value = '';
  }
</script>