@php
    $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Event Calendar')

@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* [Your existing beautiful Materio CSS remains unchanged] */
        .fc { font-family: 'Public Sans', sans-serif; }
        .fc .fc-toolbar { flex-wrap: wrap; gap: 0.5rem; padding: 0.75rem 0; }
        .fc .fc-toolbar-title { font-size: 1.25rem; font-weight: 600; color: #5f61e6; }
        .fc .fc-button { background-color: #fff; border: 1px solid #d9dee3; color: #697a8d; padding: 0.4375rem 1rem; border-radius: 0.375rem; }
        .fc .fc-button:hover, .fc .fc-button-active { background-color: #5f61e6; border-color: #5f61e6; color: #fff !important; }
        .fc .fc-daygrid-day:hover { background-color: rgba(95, 97, 230, 0.04); }
        .fc .fc-day-today { background-color: rgba(95, 97, 230, 0.08) !important; }
        .fc .fc-day-today .fc-daygrid-day-number { background-color: #5f61e6; color: #fff; border-radius: 0.375rem; }
        .fc-event { font-size: 0.75rem; padding: 2px 4px; margin-bottom: 2px; border-radius: 0.25rem; border: none; cursor: pointer; }
        .fc-event:hover { opacity: 0.85; transform: translateY(-1px); box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        .event-summary-card { border-left: 4px solid #5f61e6; transition: all 0.2s ease; }
        .event-summary-card:hover { box-shadow: 0 0.25rem 0.5rem rgba(161, 172, 184, 0.3); transform: translateX(4px); }

        @media (max-width: 768px) {
            .fc .fc-toolbar { flex-direction: column; align-items: stretch !important; }
            .fc .fc-toolbar-title { text-align: center; }
            .fc .fc-daygrid-day-frame { min-height: 60px; }
        }
    </style>
@endsection

@section('vendor-script')
    <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">
    <!-- Page Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <h4 class="mb-1">Event Calendar</h4>
                    <p class="text-muted mb-0">View approved events • Click any date to request a new permit</p>
                </div>
                <div>

                </div>
            </div>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card">
        <div class="card-header border-bottom">
            <h5 class="card-title mb-0">All Approved Events</h5>
            <small class="text-muted">Multiple venues available • Off-campus always allowed</small>
        </div>
        <div class="card-body">
            <div id="calendar"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="card mt-3">
        <div class="card-body">
            <div class="d-flex flex-wrap gap-4 justify-content-center">
                <div class="d-flex align-items-center"><span class="badge bg-label-success me-2">●</span><small>Approved Event</small></div>
                <div class="d-flex align-items-center"><span class="badge bg-label-info me-2">●</span><small>Booked Venue (others may be free)</small></div>
                <div class="d-flex align-items-center"><span class="badge bg-label-primary me-2">Click Date</span><small>Create Permit</small></div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    let selectedDates = null;
    let calendar;

    function formatDate(dateStr, includeTime = false) {
        if (!dateStr) return 'N/A';
        const date = new Date(dateStr);
        const options = { year: 'numeric', month: 'long', day: 'numeric' };
        if (includeTime) {
            options.hour = '2-digit';
            options.minute = '2-digit';
            options.hour12 = true;
        }
        return date.toLocaleDateString('en-US', options);
    }

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        selectMirror: true,
        dayMaxEvents: 3,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        buttonText: { today: 'Today', month: 'Month', week: 'Week', day: 'Day' },
        height: 'auto',
        events: '{{ route('calendar.events') }}',

        select: function(info) {
            const endDate = info.end ? new Date(info.end.getTime()) : info.start;
            selectedDates = {
                start: info.startStr.split('T')[0],
                end: endDate.toISOString().split('T')[0]
            };
            showDateSelectionSummary(selectedDates.start, selectedDates.end);
            calendar.unselect();
        },

        dateClick: function(info) {
            selectedDates = {
                start: info.dateStr,
                end: info.dateStr
            };
            showDateSelectionSummary(info.dateStr, info.dateStr);
        },

        eventClick: function(info) {
            const e = info.event;
            const p = e.extendedProps;
            const start = new Date(e.start);
            const end = e.end ? new Date(e.end) : null;
            const isAllDay = e.allDay;

            const dateDisplay = isAllDay && end
                ? `${formatDate(start)} – ${formatDate(new Date(end.getTime() - 86400000))}`
                : formatDate(start);

            const timeDisplay = isAllDay ? 'All Day' : `${start.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true})} – ${end ? end.toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true}) : 'N/A'}`;

            Swal.fire({
                icon: 'info',
                title: e.title,
                html: `
                    <div class="text-start p-3 bg-light rounded">
                        <p class="mb-2"><strong>Date:</strong> ${dateDisplay}</p>
                        <p class="mb-2"><strong>Time:</strong> ${timeDisplay}</p>
                        <p class="mb-2"><strong>Venue:</strong> ${p.venue || 'N/A'}</p>
                        <hr class="my-3">
                        <p class="mb-2"><strong>Organization:</strong> ${p.organization_name}</p>
                        <p class="mb-2"><strong>Purpose:</strong> ${p.purpose || 'N/A'}</p>
                        <p class="mb-2"><strong>Type:</strong> ${p.type}</p>
                        <span class="badge bg-success">Approved</span>
                    </div>
                `,
                width: '600px',
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    });

    calendar.render();

    // MAIN FIXED FUNCTION: Show booked venues, but NEVER block permit creation
    function showDateSelectionSummary(startDateStr, endDateStr) {
        const rangeStart = new Date(startDateStr + 'T00:00:00');
        const rangeEnd = new Date(endDateStr + 'T23:59:59');

        // Find all events overlapping the selected date(s)
        const overlappingEvents = calendar.getEvents().filter(e => {
            const eventStart = new Date(e.start);
            const eventEnd = e.end ? new Date(e.end) : new Date(eventStart.getTime() + 3600000);
            if (e.allDay) {
                eventStart.setHours(0, 0, 0, 0);
                eventEnd.setHours(23, 59, 59, 999);
            }
            return rangeStart <= eventEnd && rangeEnd >= eventStart;
        });

        const dateRangeText = startDateStr === endDateStr
            ? formatDate(startDateStr)
            : `${formatDate(startDateStr)} – ${formatDate(endDateStr)}`;

        // Group booked venues by time slot
        const bookedByTime = {};

        overlappingEvents.forEach(e => {
            const timeKey = e.allDay
                ? 'All Day'
                : `${new Date(e.start).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true})}`
                + (e.end ? ` – ${new Date(e.end).toLocaleTimeString('en-US', {hour: '2-digit', minute: '2-digit', hour12: true})}` : '');

            const venue = (e.extendedProps.venue || 'Unknown Venue').trim();
            if (!bookedByTime[timeKey]) bookedByTime[timeKey] = new Set();
            bookedByTime[timeKey].add(venue);
        });

        // Build booked venues info
        let bookedHtml = '';
        if (Object.keys(bookedByTime).length > 0) {
            bookedHtml = `
                <div class="alert alert-info mb-3">
                    <h6>Booked Venues on Selected Date(s):</h6>
                    <ul class="mb-2">
                        ${Object.entries(bookedByTime).map(([time, venues]) =>
                            `<li><strong>${time}:</strong> ${Array.from(venues).join(', ')}</li>`
                        ).join('')}
                    </ul>
                    <small class="text-success fw-bold">
                        You can still apply! Choose a different venue"
                    </small>
                </div>`;
        } else {
            bookedHtml = '<div class="alert alert-success mb-3">All campus venues are currently available!</div>';
        }

        // List all events in the range
        let eventListHtml = '';
        if (overlappingEvents.length > 0) {
            eventListHtml = overlappingEvents.map(e => {
                const start = new Date(e.start);
                const end = e.end ? new Date(e.end) : null;
                const time = e.allDay ? 'All Day' : `${start.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'})} – ${end ? end.toLocaleTimeString([], {hour: '2-digit', minute: '2-digit'}) : ''}`;
                const venue = e.extendedProps.venue || 'Not specified';
                return `
                    <div class="event-summary-card mb-3 p-3 border-start border-4 border-warning bg-light rounded">
                        <h6 class="mb-1">${e.title}</h6>
                        <small class="text-muted"><i class="ti ti-clock"></i> ${time}</small><br>
                        <small class="text-muted"><i class="ti ti-map-pin"></i> <strong>${venue}</strong> (Booked)</small><br>
                        <small class="text-muted"><i class="ti ti-users"></i> ${e.extendedProps.organization_name}</small>
                    </div>`;
            }).join('');
        } else {
            eventListHtml = '<div class="alert alert-success">No events scheduled on this date.</div>';
        }

        const isMobile = window.innerWidth < 768;

        Swal.fire({
            icon: 'calendar',
            title: 'Request Permit for:',
            html: `
                <div class="text-start">
                    <div class="badge bg-label-primary mb-3 p-2 fs-6">
                        ${dateRangeText}
                    </div>
                    ${bookedHtml}
                    <div style="max-height: ${isMobile ? '300px' : '400px'}; overflow-y: auto;">
                        ${eventListHtml}
                    </div>
                </div>
            `,
            width: isMobile ? '95%' : '720px',
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-file-text me-1"></i> Create Permit Request',
            cancelButtonText: 'Close',
            confirmButtonColor: '#5f61e6',
            cancelButtonColor: '#8592a3',
        }).then((result) => {
            if (result.isConfirmed) {
                const params = new URLSearchParams({
                    date_start: selectedDates.start,
                    date_end: selectedDates.end !== selectedDates.start ? selectedDates.end : ''
                });
                window.location.href = '{{ route('permit.form') }}?' + params.toString();
            }
        });
    }

    window.addEventListener('resize', () => calendar.updateSize());
});
</script>
@endsection
