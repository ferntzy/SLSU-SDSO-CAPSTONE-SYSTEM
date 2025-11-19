@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Event Calendar')

@section('vendor-style')
  <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
@endsection

@section('vendor-script')
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card">
      <div class="card-header d-flex justify-content-between align-items-center">
        <h5 class="mb-0">Event Calendar</h5>
      </div>
      <div class="card-body">
        <div id="calendar"></div>
      </div>
    </div>
  </div>

  <!-- Bootstrap Modal -->
  <div class="modal fade" id="eventModal" tabindex="-1" aria-labelledby="eventModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header bg-primary text-white">
          <h5 class="modal-title" id="eventModalLabel">Add Event</h5>
          <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <form id="eventForm">
            <div class="mb-3">
              <label class="form-label">Event Title</label>
              <input type="text" class="form-control" id="eventTitle" placeholder="Enter event title" required>
            </div>

            <div class="mb-3">
              <label class="form-label">Venue</label>
              <select class="form-select" id="venueSelect" required>
                <option value="">Select Venue</option>
                <option value="Gym">Gym</option>
                <option value="Auditorium">Auditorium</option>
                <option value="Classroom">Classroom</option>
                <option value="Court">Court</option>
                <option value="Field">Field</option>
              </select>
            </div>

            <div class="mb-3">
              <label class="form-label">Start Time</label>
              <input type="time" class="form-control" id="eventStartTime" required>
            </div>

            <div class="mb-3">
              <label class="form-label">End Time</label>
              <input type="time" class="form-control" id="eventEndTime">
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
          <button type="button" id="saveEventBtn" class="btn btn-primary">Save Event</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const calendarEl = document.getElementById('calendar');
      const modal = new bootstrap.Modal(document.getElementById('eventModal'));
      const eventTitleInput = document.getElementById('eventTitle');
      const venueSelect = document.getElementById('venueSelect');
      const eventStartTimeInput = document.getElementById('eventStartTime');
      const eventEndTimeInput = document.getElementById('eventEndTime');
      const saveEventBtn = document.getElementById('saveEventBtn');

      let currentDate = null;
      let storedEvents = JSON.parse(localStorage.getItem('calendarEvents')) || [];

      const calendar = new FullCalendar.Calendar(calendarEl, {
        selectable: true,
        editable: true,
        headerToolbar: {
          left: 'prev,next today',
          center: 'title',
          right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: storedEvents,
        dateClick: function (info) {
          currentDate = info.dateStr;
          eventTitleInput.value = '';
          venueSelect.value = '';
          eventStartTimeInput.value = '';
          eventEndTimeInput.value = '';
          modal.show();
        },
        eventClick: function (info) {
          Swal.fire({
            title: info.event.title,
            html: `
                    <b>Venue:</b> ${info.event.extendedProps.venue || 'N/A'}<br>
                    <b>Time:</b> ${formatTime(info.event.start)} - ${formatTime(info.event.end)}
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

      // Save event button
      saveEventBtn.addEventListener('click', function () {
        const title = eventTitleInput.value.trim();
        const venue = venueSelect.value.trim();
        const startTime = eventStartTimeInput.value;
        const endTime = eventEndTimeInput.value;

        if (!title || !venue || !startTime) {
          Swal.fire('Missing Fields', 'Please complete all required fields.', 'warning');
          return;
        }

        const start = `${currentDate}T${startTime}`;
        const end = endTime ? `${currentDate}T${endTime}` : null;

        calendar.addEvent({
          id: Date.now().toString(),
          title,
          start,
          end,
          extendedProps: { venue }
        });

        saveEvents();
        modal.hide();

        Swal.fire('Success!', 'Event added successfully.', 'success');
      });

      // Save events to localStorage
      function saveEvents() {
        const allEvents = calendar.getEvents().map(e => ({
          id: e.id,
          title: e.title,
          start: e.startStr,
          end: e.endStr,
          venue: e.extendedProps.venue
        }));
        localStorage.setItem('calendarEvents', JSON.stringify(allEvents));
      }

      function formatTime(dateObj) {
        if (!dateObj) return '';
        const d = new Date(dateObj);
        return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
      }
    });
  </script>
@endsection