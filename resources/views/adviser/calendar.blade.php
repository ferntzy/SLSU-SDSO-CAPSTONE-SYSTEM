@php $container = 'container-xxl'; @endphp

@extends('layouts.adviserLayout')

@section('title', 'Event Calendar - Faculty Adviser')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .fc { font-family: 'Public Sans', sans-serif; }
    .fc .fc-toolbar {
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 1rem !important;
    }
    .fc .fc-toolbar-title {
        font-size: 1.4rem !important;
        font-weight: 700;
        color: #5f61e6;
    }
    .fc .fc-button {
        padding: 0.5rem 0.9rem !important;
        font-size: 0.9rem !important;
        border-radius: 0.5rem !important;
    }

    /* Fully responsive day grid */
    .fc .fc-daygrid-day-frame { min-height: 100px !important; }
    .fc .fc-daygrid-day-top { font-size: 0.9rem; }

    .fc .fc-day-today {
        background: rgba(95, 97, 230, 0.15) !important;
        border-radius: 12px;
        position: relative;
    }
    .fc .fc-day-today .fc-daygrid-day-number {
        background: #5f61e6;
        color: #fff;
        border-radius: 50%;
        width: 36px;
        height: 36px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto;
        font-weight: 700;
    }

    .fc-event {
        background: linear-gradient(135deg, #1e7e34, #28a745) !important;
        border: none !important;
        color: #fff !important;
        font-weight: 600;
        font-size: 0.78rem !important;
        padding: 5px 8px !important;
        border-radius: 8px !important;
        box-shadow: 0 3px 10px rgba(30,126,52,0.3);
        transition: all 0.3s ease;
        white-space: normal !important;
        line-height: 1.3;
    }
    .fc-event:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 10px 20px rgba(30,126,52,0.4) !important;
    }

    .fc .fc-daygrid-day:hover {
        background: rgba(95, 97, 230, 0.08);
        border-radius: 12px;
        cursor: pointer;
    }

    /* Event list in modal */
    .event-item {
        background: #f8fff9;
        border-left: 6px solid #28a745;
        border-radius: 10px;
        transition: all 0.3s ease;
        overflow: hidden;
    }
    .event-item:hover {
        transform: translateX(10px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        background: #e8f7ec;
    }

    /* Responsive SweetAlert */
    @media (max-width: 768px) {
        .fc .fc-toolbar { flex-direction: column; text-align: center; }
        .fc .fc-toolbar-title { font-size: 1.2rem !important; margin: 0.5rem 0 !important; }
        .fc .fc-button { font-size: 0.85rem !important; padding: 0.4rem 0.8rem !important; }
        .fc-event { font-size: 0.72rem !important; padding: 4px 6px !important; }
        .fc .fc-daygrid-day-frame { min-height: 80px !important; }
    }

    @media (max-width: 480px) {
        .fc .fc-toolbar-chunk { margin: 0.3rem 0 !important; }
        .fc-event { font-size: 0.7rem !important; }
    }
</style>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">
    <!-- Header -->
    <div class="text-center mb-5 px-3">
        <h2 class="fw-bold text-primary mb-2">Event Calendar</h2>
        <p class="text-muted fs-6 mb-3">
            Click any date to view all <strong class="text-success">fully approved</strong> events
        </p>
        <div class="d-inline-block">
            <span class="badge bg-success rounded-pill fs-6 px-4 py-2 shadow-sm">
                {{ Auth::user()->advisedOrganizations()->count() }} Organization{{ Auth::user()->advisedOrganizations()->count() !== 1 ? 's' : '' }} Advised
            </span>
        </div>
    </div>

    <!-- Calendar Card -->
    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div id="adviserCalendar" class="fc fc-theme-standard"></div>
        </div>
    </div>

    <!-- Legend -->
    <div class="text-center mt-4">
        <div class="d-inline-flex flex-column flex-sm-row align-items-center gap-3 bg-light px-4 py-3 rounded-pill shadow">
            <div class="d-flex align-items-center">
                <span class="badge bg-success rounded-circle me-2" style="width:14px;height:14px;"></span>
                <span class="text-success fw-semibold">Fully Approved by VP-SAS</span>
            </div>
            <div class="d-flex align-items-center text-primary">
                <i class="ti ti-calendar-event me-2"></i>
                <span class="fw-semibold">Tap any date to view events</span>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('adviserCalendar');
    let allEvents = [];

    const calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: window.innerWidth < 768 ? 'dayGridMonth' : 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        buttonText: { today: 'Today', month: 'Month', week: 'Week' },
        height: 'auto',
        contentHeight: 'auto',
        aspectRatio: 1.35,
        dayMaxEvents: 3,
        eventDisplay: 'block',
        editable: false,
        selectable: false,
        dayCellContent: function(info) {
            return { html: `<div class="text-center">${info.dayNumberText}</div>` };
        },

        events: '{{ route('adviser.calendar.events') }}',

        eventsSet: function(events) {
            allEvents = events;
        },

        dateClick: function(info) {
            const clickedDate = info.dateStr;

            const eventsOnDate = allEvents.filter(event => {
                const s = new Date(event.start);
                const e = event.end ? new Date(event.end) : s;
                const start = s.toISOString().split('T')[0];
                let end = e.toISOString().split('T')[0];
                if (event.allDay && event.end) {
                    const endDate = new Date(e);
                    endDate.setDate(endDate.getDate() - 1);
                    end = endDate.toISOString().split('T')[0];
                }
                return clickedDate >= start && clickedDate <= end;
            });

            const dateFormatted = new Date(clickedDate).toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            if (eventsOnDate.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Events',
                    html: `<p class="mb-0">No approved events on <strong>${dateFormatted}</strong></p>`,
                    confirmButtonColor: '#5f61e6'
                });
                return;
            }

            eventsOnDate.sort((a, b) => new Date(a.start) - new Date(b.start));

            let eventsHtml = '';
            eventsOnDate.forEach(e => {
                const p = e.extendedProps;
                const start = new Date(e.start);
                const end = e.end ? new Date(e.end) : null;

                let timeDisplay = 'All Day';
                if (!e.allDay) {
                    const fmt = d => d.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                    timeDisplay = `${fmt(start)} ${end ? '– ' + fmt(end) : ''}`;
                }

                eventsHtml += `
                    <div class="event-item p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <h6 class="fw-bold text-success mb-0">${e.title}</h6>
                            <span class="badge bg-success small">Approved</span>
                        </div>
                        <div class="row g-2 small">
                            <div class="col-12 col-sm-6">
                                <i class="ti ti-clock text-primary me-1"></i>
                                <strong>Time:</strong> ${timeDisplay}
                            </div>
                            <div class="col-12 col-sm-6">
                                <i class="ti ti-map-pin text-primary me-1"></i>
                                <strong>Venue:</strong> <span class="fw-semibold">${p.venue || 'Not specified'}</span>
                            </div>
                            <div class="col-12">
                                <i class="ti ti-users text-primary me-1"></i>
                                <strong>Org:</strong>
                                <span class="badge bg-primary small">${p.organization_name}</span>
                            </div>
                            ${p.purpose ? `
                            <div class="col-12 mt-2">
                                <div class="bg-white p-3 rounded small text-muted border">
                                    ${p.purpose}
                                </div>
                            </div>` : ''}
                        </div>
                    </div>`;
            });

            const isMobile = window.innerWidth < 768;
            Swal.fire({
                title: `<div class="text-center">
                    <div class="text-primary fs-4 fw-bold">${eventsOnDate.length} Event${eventsOnDate.length > 1 ? 's' : ''}</div>
                    <small class="text-muted">${dateFormatted}</small>
                </div>`,
                html: `<div class="${isMobile ? 'px-2' : 'px-4'}" style="max-height: ${isMobile ? '70vh' : '65vh'}; overflow-y: auto;">
                    ${eventsHtml}
                </div>`,
                width: isMobile ? '95%' : '800px',
                showCloseButton: true,
                showConfirmButton: false,
                customClass: { popup: 'shadow-xl border-0' }
            });
        },

        eventClick: function(info) {
            info.jsEvent.preventDefault();
            info.jsEvent.stopPropagation();
        }
    });

    calendar.render();

    // Re-render on resize for perfect responsiveness
    window.addEventListener('resize', () => {
        calendar.updateSize();
    });
});
</script>
@endsection
