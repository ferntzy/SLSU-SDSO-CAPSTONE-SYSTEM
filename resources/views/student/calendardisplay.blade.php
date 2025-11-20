@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Event Calendar')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endsection

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Event Calendar</h5>
        <small class="text-muted">Click on a date or drag to select multiple dates to create an event</small>
      </div>
      <div class="card-body">
        <div id="calendar"></div>
      </div>
    </div>
  </div>

  <!-- Event Permit Form Modal -->
  <div class="modal fade" id="permitModal" tabindex="-1" aria-labelledby="permitModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="permitModalLabel">SDSO Organization Activity Permit</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
          <form id="permitForm">
            @csrf

            {{-- Basic Info --}}
            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
              </div>


              <div class="col-md-6">
              <label class="form-label">Organization</label>
              <select name="organization_id" class="form-select" required>
                @foreach ($organizations as $org)
                  <option value="{{ $org->organization_id }}">{{ $org->organization_name }}</option>
                @endforeach
              </select>
            </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Title of Activity</label>
              <input type="text" name="title_activity" class="form-control" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Purpose</label>
              <textarea name="purpose" class="form-control" rows="3" required></textarea>
            </div>

            <hr class="my-4">

            {{-- Type of Event --}}
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

            {{-- Nature of Activity --}}
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
                        id="nature_{{ $loop->index }}" required>
                      <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $nature }}</label>
                    </div>
                  </div>
                @endforeach

                <div class="col-md-6 mt-2">
                  <div class="input-group">
                    <div class="input-group-text">
                      <input class="form-check-input mt-0" type="radio" name="nature" value="Other" id="nature_other">
                    </div>
                    <input type="text" name="nature_other" id="nature_other_text" class="form-control" placeholder="Other (specify)" disabled>
                  </div>
                </div>
              </div>
            </div>

            <hr class="my-4">

            {{-- Venue & Schedule --}}
            <div class="row mb-3">
              <div class="col-md-4">
                <label class="form-label">Venue</label>
                <input type="text" name="venue" class="form-control" required>
              </div>
              <div class="col-md-4">
                <label class="form-label">Start Date</label>
                <input type="text" id="date_start" name="date_start" class="form-control" placeholder="Select start date" required readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">End Date</label>
                <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Select end date" readonly>
              </div>
            </div>

            <div class="row mb-3">
              <div class="col-md-6">
                <label class="form-label">Start Time</label>
                <input type="text" id="time_start" name="time_start" class="form-control" placeholder="Select start time" required>
              </div>
              <div class="col-md-6">
                <label class="form-label">End Time</label>
                <input type="text" id="time_end" name="time_end" class="form-control" placeholder="Select end time" required>
              </div>
            </div>

            {{-- Participants --}}
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
                  <input class="form-check-input mt-0" type="radio" id="participants_other_check" name="participants" value="Other">
                </div>
                <input type="text" class="form-control" name="participants_other" id="participants_other_text" placeholder="Specify other participants" disabled>
              </div>
            </div>

            <div class="mb-3">
              <label class="form-label">Number of Participants</label>
              <input type="number" name="number" class="form-control" min="1" required>
            </div>

            <hr class="my-4">

            {{-- Signature --}}
            <div class="mb-3">
              <label class="form-label fw-bold">Signature</label>
              <small class="text-muted d-block mb-2">You can either upload a signature image or draw one below.</small>
              <input type="file" name="signature_upload" id="signature_upload" accept="image/*" class="form-control mb-2">
              <canvas id="signature-pad" style="border: 1px solid #ccc; width: 100%; height: 200px; touch-action: none;"></canvas>
              <div class="mt-2">
                <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear Signature</button>
              </div>
              <input type="hidden" name="signature_data" id="signature_data">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="submitPermitBtn" class="btn btn-primary">Submit Permit</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const permitModal = new bootstrap.Modal(document.getElementById('permitModal'));
      let storedEvents = JSON.parse(localStorage.getItem('calendarEvents')) || [];
      let selectedDates = [];

      // Initialize Calendar
      const calendar = new FullCalendar.Calendar(calendarEl, {
        selectable: true,
        selectMirror: true,
        editable: true,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: storedEvents,

        // Handle date selection (single or multiple consecutive dates)
        select: function(info) {
          selectedDates = {
            start: info.startStr.split('T')[0],
            end: info.end ? new Date(info.end.getTime() - 86400000).toISOString().split('T')[0] : info.startStr.split('T')[0]
          };

          // Open the permit form modal
          openPermitForm();
          calendar.unselect();
        },

        // Handle event click to view/delete
        eventClick: function (info) {
          Swal.fire({
            title: info.event.title,
            html: `
              <b>Organization:</b> ${info.event.extendedProps.organization || 'N/A'}<br>
              <b>Venue:</b> ${info.event.extendedProps.venue || 'N/A'}<br>
              <b>Date:</b> ${formatDate(info.event.start)} ${info.event.end ? '- ' + formatDate(info.event.end) : ''}<br>
              <b>Time:</b> ${info.event.extendedProps.time_start || 'N/A'} - ${info.event.extendedProps.time_end || 'N/A'}
            `,
            showCancelButton: true,
            confirmButtonText: 'Delete',
            confirmButtonColor: '#d33',
            cancelButtonText: 'Close'
          }).then((result) => {
            if (result.isConfirmed) {
              info.event.remove();
              saveEvents();
              Swal.fire('Deleted!', 'Your event has been removed.', 'success');
            }
          });
        },
        eventDrop: saveEvents,
        eventResize: saveEvents
      });

      calendar.render();

      // Open Permit Form
      function openPermitForm() {
        // Pre-fill dates
        document.getElementById('date_start').value = selectedDates.start;
        document.getElementById('date_end').value = selectedDates.end !== selectedDates.start ? selectedDates.end : '';

        // Reset form
        document.getElementById('permitForm').reset();
        document.getElementById('date_start').value = selectedDates.start;
        document.getElementById('date_end').value = selectedDates.end !== selectedDates.start ? selectedDates.end : '';
        signaturePad.clear();

        permitModal.show();
      }

      // Initialize Flatpickr for time selection
      flatpickr("#time_start", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
      });

      flatpickr("#time_end", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
      });

      // Enable/disable "Other" inputs
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
      toggleOtherField('nature', 'nature_other_text');
      toggleOtherField('participants', 'participants_other_text');

      // Signature Pad Setup
      const canvas = document.getElementById('signature-pad');
      const signaturePad = new SignaturePad(canvas, {
        backgroundColor: 'rgba(255, 255, 255, 0)',
        penColor: 'black',
        minWidth: 1,
        maxWidth: 3,
      });

      function resizeCanvas() {
        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);
        signaturePad.clear();
      }
      resizeCanvas();

      document.getElementById('clear-signature').addEventListener('click', function () {
        signaturePad.clear();
      });

      // Submit Permit Form
      document.getElementById('submitPermitBtn').addEventListener('click', function () {
        const form = document.getElementById('permitForm');

        if (!form.checkValidity()) {
          form.reportValidity();
          return;
        }

        const formData = new FormData(form);

        // Get nature value (handle "Other" option)
        let natureValue = formData.get('nature');
        if (natureValue === 'Other') {
          natureValue = formData.get('nature_other') || 'Other';
        }

        // Get participants value (handle "Other" option)
        let participantsValue = formData.get('participants');
        if (participantsValue === 'Other') {
          participantsValue = formData.get('participants_other') || 'Other';
        }

        // Save signature if drawn
        if (!signaturePad.isEmpty()) {
          const dataURL = signaturePad.toDataURL('image/png');
          document.getElementById('signature_data').value = dataURL;
        }

        // Create event object
        const newEvent = {
          id: Date.now().toString(),
          title: formData.get('title_activity'),
          start: formData.get('date_start'),
          end: formData.get('date_end') || formData.get('date_start'),
          extendedProps: {
            organization: formData.get('organization_name'),
            venue: formData.get('venue'),
            purpose: formData.get('purpose'),
            type: formData.get('type'),
            nature: natureValue,
            participants: participantsValue,
            number: formData.get('number'),
            time_start: formData.get('time_start'),
            time_end: formData.get('time_end'),
            signature_data: document.getElementById('signature_data').value
          }
        };

        // Add event to calendar
        calendar.addEvent(newEvent);
        saveEvents();

        // Close modal and show success message
        permitModal.hide();
        Swal.fire('Success!', 'Event permit submitted successfully.', 'success');

        // Here you can also send the data to your server
        // submitToServer(formData);
      });

      // Save events to localStorage
      function saveEvents() {
        const allEvents = calendar.getEvents().map(e => ({
          id: e.id,
          title: e.title,
          start: e.startStr,
          end: e.endStr,
          ...e.extendedProps
        }));
        localStorage.setItem('calendarEvents', JSON.stringify(allEvents));
      }

      function formatDate(dateObj) {
        if (!dateObj) return '';
        return new Date(dateObj).toLocaleDateString('en-US', {
          year: 'numeric',
          month: 'long',
          day: 'numeric'
        });
      }
    });
  </script>
@endsection
