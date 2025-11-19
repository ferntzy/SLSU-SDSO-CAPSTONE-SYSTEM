@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Organization Activity Permit Form')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <!-- Added Signature Pad library for better signature drawing -->
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endsection

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card">
      <div class="card-header bg-primary text-white">
        <h5 class="mb-0">SDSO Organization Activity Permit</h5>
      </div>

      <div class="card-body">
        @if ($errors->any())
          <div class="alert alert-danger">
            <ul class="mb-0">
              @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
              @endforeach
            </ul>
          </div>
        @endif

        <form action="{{ route('permit.generate') }}" method="POST" enctype="multipart/form-data" id="permitForm">
          @csrf

          {{-- Basic Info --}}
          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Name</label>
              <!-- Auto-populated with logged-in user's name, but editable -->
              <input type="text" name="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
            </div>
            <div class="col-md-6">
              <label class="form-label">Organization</label>
              <select name="organization_id" class="form-select" required>
                <option value="" disabled selected>Select your organization</option>
                @foreach ($organizations as $org)
                  <option value="{{ $org->organization_id }}">
                    {{ $org->organization_name }}
                  </option>
                @endforeach
              </select>
            </div>

          </div>

          <div class="mb-3">
            <label class="form-label">Title of Activity</label>
            <input type="text" name="title_activity" class="form-control">
          </div>

          <div class="mb-3">
            <label class="form-label">Purpose</label>
            <textarea name="purpose" class="form-control" rows="3"></textarea>
          </div>

          <hr class="my-4">

          {{-- Type of Event (Radio buttons for single selection) --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Type of Event</label>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="type" value="In-Campus" id="type1" required>
              <label class="form-check-label" for="type1">In-Campus</label>
            </div>
            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="type" value="Off-Campus" id="type2">
              <label class="form-check-label" for="type2">Off-Campus</label>
            </div>
          </div>

          {{-- Nature of Activity (Radio buttons for single selection) --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Nature of Activity</label>
            <div class="row">
              @php
                $natures = [
                  'Training/Seminar',
                  'Conference/Summit',
                  'Culmination',
                  'Socialization',
                  'Meeting',
                  'Concert',
                  'Exhibit',
                  'Program',
                  'Educational Tour',
                  'Clean and Green',
                  'Competition'
                ];
              @endphp
              @foreach ($natures as $nature)
                <div class="col-md-4">
                  <div class="form-check">
                    <input class="form-check-input" type="radio" name="nature" value="{{ $nature }}"
                      id="nature_{{ $loop->index }}">
                    <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $nature }}</label>
                  </div>
                </div>
              @endforeach

              <div class="col-md-6 mt-2">
                <div class="input-group">
                  <div class="input-group-text">
                    <input class="form-check-input mt-0" type="radio" name="nature" value="Other" id="nature_other">
                  </div>
                  <input type="text" name="nature_other" class="form-control" placeholder="Other (specify)" disabled>
                </div>
              </div>
            </div>
          </div>

          <hr class="my-4">

          {{-- Venue & Schedule --}}
          <div class="row mb-3">
            <div class="col-md-4">
              <label class="form-label">Venue</label>
              <input type="text" name="venue" class="form-control">
            </div>
            <div class="col-md-4">
              <label class="form-label">Start Date</label>
              <input type="text" id="date_start" name="date_start" class="form-control" placeholder="Select start date"
                required>
            </div>
            <div class="col-md-4">
              <label class="form-label">End Date (optional)</label>
              <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Select end date">
            </div>
          </div>

          <div class="row mb-3">
            <div class="col-md-6">
              <label class="form-label">Start Time</label>
              <input type="text" id="time_start" name="time_start" class="form-control" placeholder="Select start time"
                required>
            </div>
            <div class="col-md-6">
              <label class="form-label">End Time</label>
              <input type="text" id="time_end" name="time_end" class="form-control" placeholder="Select end time"
                required>
            </div>
          </div>

          {{-- Participants (Radio buttons for single selection) --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Participants</label><br>

            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="participants" value="Members" id="members" required>
              <label class="form-check-label" for="members">Members</label>
            </div>

            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="participants" value="Officers" id="officers">
              <label class="form-check-label" for="officers">Officers</label>
            </div>

            <div class="form-check form-check-inline">
              <input class="form-check-input" type="radio" name="participants" value="All Students" id="all_students">
              <label class="form-check-label" for="all_students">All Students</label>
            </div>

            <div class="input-group mt-2" style="max-width: 400px;">
              <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" id="participants_other_check" name="participants"
                  value="Other">
              </div>
              <input type="text" class="form-control" name="participants_other" id="participants_other_text"
                placeholder="Specify other participants" disabled>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Number of Participants</label>
            <input type="number" name="number" class="form-control" min="1">
          </div>

          <hr class="my-4">

          {{-- Signature --}}
          <div class="mb-3">
            <label class="form-label fw-bold">Signature</label>
            <small class="text-muted d-block mb-2">You can either upload a signature image or draw one below.</small>
            <input type="file" name="signature_upload" accept="image/*" class="form-control mb-2">
            <!-- Upgraded to Signature Pad library for better drawing -->
            <canvas id="signature-pad"
              style="border: 1px solid #ccc; width: 100%; height: 200px; touch-action: none;"></canvas>
            <div class="mt-2">
              <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear Signature</button>
              <button type="button" class="btn btn-sm btn-info" id="resize-signature">Resize Canvas</button>
            </div>
            <input type="hidden" name="signature_data" id="signature_data">
          </div>

          <div class="text-end">
            <button type="submit" class="btn btn-primary">Generate PDF</button>
          </div>

        </form>
      </div>
    </div>
  </div>

  {{-- JS --}}
  <script>
    // ✅ Flatpickr Initialization
    document.addEventListener('DOMContentLoaded', function () {
      flatpickr("#date_start", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
      });

      flatpickr("#date_end", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
      });

      flatpickr("#time_start", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K", // 12-hour with AM/PM
        time_24hr: false
      });

      flatpickr("#time_end", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
      });

      // ✅ Enable/disable "Other" inputs dynamically for Nature and Participants
      function toggleOtherField(radioName, textFieldId) {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        const textField = document.getElementById(textFieldId);
        radios.forEach(radio => {
          radio.addEventListener('change', function () {
            textField.disabled = this.value !== 'Other';
            textField.required = this.value === 'Other';
            if (this.value !== 'Other') textField.value = '';
          });
        });
      }
      toggleOtherField('nature', 'nature_other');
      toggleOtherField('participants', 'participants_other_text');

      // ✅ Signature Pad Logic (Upgraded with Signature Pad library)
      const canvas = document.getElementById('signature-pad');
      const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)', // Transparent background
        penColor: 'black',
        minWidth: 1,
        maxWidth: 3,
      });

      // Resize canvas for high-DPI displays
      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear(); // Clear after resize
      }
      resizeCanvas();
      window.addEventListener('resize', resizeCanvas);

      document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
      });

      document.getElementById('resize-signature').addEventListener('click', function () {
        resizeCanvas();
      });

      document.getElementById('permitForm').addEventListener('submit', function () {
        if (!signaturePad.isEmpty()) {
          const dataURL = signaturePad.toDataURL('image/png');
          document.getElementById('signature_data').value = dataURL;
        }
      });
    });
  </script>
@endsection