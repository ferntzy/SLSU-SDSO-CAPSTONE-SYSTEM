@php
    $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'Event Calendar')

@section('vendor-style')
    <link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
    <style>
        /* Materio-themed Calendar Customizations */
        .fc {
            font-family: 'Public Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Oxygen, Ubuntu, Cantarell, 'Fira Sans', 'Droid Sans', 'Helvetica Neue', sans-serif;
        }

        /* Calendar header styling */
        .fc .fc-toolbar {
            flex-wrap: wrap;
            gap: 0.5rem;
            padding: 0.75rem 0;
        }

        .fc .fc-toolbar-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #5f61e6;
        }

        .fc .fc-button {
            background-color: #fff;
            border: 1px solid #d9dee3;
            color: #697a8d;
            padding: 0.4375rem 1rem;
            font-size: 0.9375rem;
            font-weight: 500;
            border-radius: 0.375rem;
            transition: all 0.2s ease-in-out;
        }

        .fc .fc-button:hover {
            background-color: #5f61e6;
            border-color: #5f61e6;
            color: #fff;
        }

        .fc .fc-button-active {
            background-color: #5f61e6 !important;
            border-color: #5f61e6 !important;
            color: #fff !important;
        }

        /* Day cells */
        .fc .fc-daygrid-day {
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .fc .fc-daygrid-day:hover {
            background-color: rgba(95, 97, 230, 0.04);
        }

        .fc .fc-daygrid-day-frame {
            min-height: 100px;
        }

        .fc .fc-daygrid-day-top {
            padding: 0.5rem;
        }

        .fc .fc-daygrid-day-number {
            padding: 0.25rem 0.5rem;
            font-weight: 500;
            color: #697a8d;
        }

        .fc .fc-day-today {
            background-color: rgba(95, 97, 230, 0.08) !important;
        }

        .fc .fc-day-today .fc-daygrid-day-number {
            background-color: #5f61e6;
            color: #fff;
            border-radius: 0.375rem;
            padding: 0.25rem 0.5rem;
        }

        /* Events styling */
        .fc-event {
            font-size: 0.75rem;
            padding: 2px 4px;
            margin-bottom: 2px;
            border-radius: 0.25rem;
            border: none;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .fc-event:hover {
            opacity: 0.85;
            transform: translateY(-1px);
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .fc-event-title {
            font-weight: 500;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        /* More link */
        .fc .fc-daygrid-more-link {
            font-size: 0.7rem;
            font-weight: 600;
            color: #5f61e6;
            padding: 2px 4px;
            background: rgba(95, 97, 230, 0.1);
            border-radius: 0.25rem;
            margin-top: 2px;
        }

        .fc .fc-daygrid-more-link:hover {
            background: rgba(95, 97, 230, 0.2);
            text-decoration: none;
        }

        /* Popover for more events */
        .fc .fc-popover {
            border-radius: 0.5rem;
            box-shadow: 0 0.25rem 1rem rgba(161, 172, 184, 0.45);
            border: none;
        }

        .fc .fc-popover-header {
            background-color: #5f61e6;
            color: #fff;
            padding: 0.75rem 1rem;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        /* Mobile responsiveness */
        @media (max-width: 768px) {
            .fc .fc-toolbar {
                flex-direction: column;
                align-items: stretch !important;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                justify-content: center;
                margin-bottom: 0.5rem;
            }

            .fc .fc-toolbar-title {
                font-size: 1.1rem;
                text-align: center;
                margin: 0.5rem 0;
            }

            .fc .fc-button {
                font-size: 0.8125rem;
                padding: 0.375rem 0.75rem;
            }

            .fc .fc-daygrid-day-frame {
                min-height: 60px;
            }

            .fc-event {
                font-size: 0.7rem;
                padding: 1px 3px;
            }

            /* Hide day names on very small screens */
            @media (max-width: 576px) {
                .fc .fc-col-header-cell-cushion {
                    font-size: 0.75rem;
                }
            }
        }

        /* SweetAlert2 Materio styling */
        .swal2-popup {
            border-radius: 0.5rem;
            font-family: 'Public Sans', sans-serif;
        }

        .swal2-title {
            color: #5f61e6;
            font-weight: 600;
        }

        .swal2-confirm {
            background-color: #5f61e6 !important;
            border-radius: 0.375rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        .swal2-cancel {
            border-radius: 0.375rem;
            padding: 0.5rem 1.5rem;
            font-weight: 500;
        }

        /* Event cards in summary */
        .event-summary-card {
            border-left: 4px solid #5f61e6;
            transition: all 0.2s ease;
        }

        .event-summary-card:hover {
            box-shadow: 0 0.25rem 0.5rem rgba(161, 172, 184, 0.3);
            transform: translateX(4px);
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
                        <h4 class="mb-1">📅 Event Calendar</h4>
                        <p class="text-muted mb-0">View all approved events and create new permit requests</p>
                    </div>
                    <div>
                        <a href="{{ route('permit.form') }}" class="btn btn-primary">
                            <i class="ti ti-plus me-1"></i>
                            <span class="d-none d-sm-inline">New Permit</span>
                            <span class="d-inline d-sm-none">New</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Calendar Card -->
        <div class="card">
            <div class="card-header border-bottom">
                <h5 class="card-title mb-0">All Approved Events</h5>
                <small class="text-muted">Click on a date to view events or create a new permit</small>
            </div>
            <div class="card-body">
                <div id="calendar"></div>
            </div>
        </div>

        <!-- Legend Card (Mobile-friendly) -->
        <div class="card mt-3">
            <div class="card-body">
                <div class="d-flex flex-wrap gap-3 justify-content-center">
                    <div class="d-flex align-items-center">
                        <span class="badge bg-label-success me-2">●</span>
                        <small class="text-muted">Approved Events</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <span class="badge bg-label-primary me-2">📍</span>
                        <small class="text-muted">Click date to view/create</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const calendarEl = document.getElementById('calendar');
            let selectedDates = null;

            function formatDate(dateObj, includeTime = false) {
                if (!dateObj) return 'N/A';
                const date = new Date(dateObj);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                }) + (includeTime ? ' ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '');
            }

            function getEventsForRange(startStr, endStr) {
                const events = calendar.getEvents();
                const start = new Date(startStr);
                const end = new Date(endStr);
                end.setDate(end.getDate() - 1);

                return events.filter(event => {
                    const eventStart = new Date(event.start);
                    const eventEnd = event.end ? new Date(event.end) : new Date(event.start);
                    if (event.allDay && event.end) eventEnd.setDate(eventEnd.getDate() - 1);

                    return eventStart <= end && eventEnd >= start;
                }).map(event => ({
                    title: event.title,
                    start: event.start,
                    end: event.end,
                    props: event.extendedProps,
                    color: event.backgroundColor || event.color || '#28a745',
                    allDay: event.allDay
                }));
            }

            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                selectable: true,
                selectMirror: true,
                editable: false,
                dayMaxEvents: 3, // Show max 3 events per day
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,timeGridDay'
                },
                buttonText: {
                    today: 'Today',
                    month: 'Month',
                    week: 'Week',
                    day: 'Day'
                },
                height: 'auto',
                contentHeight: 'auto',
                aspectRatio: 1.8,

                // Fetch ALL approved events from database
                events: function(fetchInfo, successCallback, failureCallback) {
                    fetch('{{ route('calendar.events') }}')
                        .then(response => response.json())
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            Swal.fire({
                                icon: 'error',
                                title: 'Error Loading Events',
                                text: 'Could not load calendar events. Please refresh the page.',
                                confirmButtonColor: '#5f61e6'
                            });
                            failureCallback(error);
                        });
                },

                // Handle date selection (drag to select range)
                select: function(info) {
                    const actualEnd = info.end ? new Date(info.end.getTime() - 86400000).toISOString().split('T')[0] : info.startStr.split('T')[0];

                    selectedDates = {
                        start: info.startStr.split('T')[0],
                        end: actualEnd
                    };

                    showDateSelectionSummary(selectedDates.start, selectedDates.end);
                    calendar.unselect();
                },

                // Handle single date click
                dateClick: function(info) {
                    if (info.dayEl && info.dateStr) {
                        selectedDates = {
                            start: info.dateStr,
                            end: info.dateStr
                        };
                        showDateSelectionSummary(info.dateStr, info.dateStr);
                    }
                },

                // Handle event click (view details)
                eventClick: function (info) {
                    const event = info.event;
                    const props = event.extendedProps;
                    const eventStart = new Date(event.start);

                    const isAllDay = event.allDay || (event.startStr.indexOf('T') === -1);

                    let dateDisplay;
                    let timeDisplay;

                    if (isAllDay) {
                        const eventEnd = event.end ? new Date(event.end.getTime() - 86400000) : null;
                        dateDisplay = eventEnd ? `${formatDate(eventStart)} - ${formatDate(eventEnd)}` : formatDate(eventStart);
                        timeDisplay = 'All Day';
                    } else {
                        const eventEnd = event.end ? new Date(event.end) : null;
                        dateDisplay = formatDate(eventStart);
                        timeDisplay = `${eventStart.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })} - ${eventEnd ? eventEnd.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'N/A'}`;
                    }

                    Swal.fire({
                        icon: 'info',
                        title: event.title,
                        html: `
                            <div class="text-start p-3 bg-light rounded">
                                <p class="mb-2"><i class="ti ti-calendar me-2 text-primary"></i><strong>Date(s):</strong> ${dateDisplay}</p>
                                <p class="mb-2"><i class="ti ti-clock me-2 text-primary"></i><strong>Time:</strong> ${timeDisplay}</p>
                                <p class="mb-2"><i class="ti ti-map-pin me-2 text-primary"></i><strong>Venue:</strong> ${props.venue || 'N/A'}</p>
                                <hr class="my-2">
                                <p class="mb-2"><i class="ti ti-users me-2 text-primary"></i><strong>Organization:</strong> ${props.organization_name || 'N/A'}</p>
                                <p class="mb-2"><i class="ti ti-clipboard-text me-2 text-primary"></i><strong>Purpose:</strong> ${props.purpose || 'N/A'}</p>
                                <p class="mb-2"><i class="ti ti-info-circle me-2 text-primary"></i><strong>Type:</strong> ${props.type || 'N/A'}</p>
                                <div class="mt-3">
                                    <span class="badge bg-success">✓ Approved</span>
                                </div>
                            </div>
                        `,
                        showCloseButton: true,
                        showConfirmButton: false,
                        width: window.innerWidth < 768 ? '95%' : '600px',
                        confirmButtonColor: '#5f61e6'
                    });
                }
            });

            calendar.render();

            // Show date selection summary with events
            function showDateSelectionSummary(startDateStr, endDateStr) {
                const eventsInSelection = getEventsForRange(startDateStr, endDateStr);

                let dateRangeText = formatDate(startDateStr);
                if (startDateStr !== endDateStr) {
                    dateRangeText = `${formatDate(startDateStr)} to ${formatDate(endDateStr)}`;
                }

                // Group events by venue
                const eventsByVenue = eventsInSelection.reduce((groups, event) => {
                    const venue = event.props.venue || 'Venue N/A';
                    if (!groups[venue]) {
                        groups[venue] = [];
                    }
                    groups[venue].push(event);
                    return groups;
                }, {});

                let eventListHtml = '';
                if (eventsInSelection.length > 0) {
                    const sortedVenueGroups = Object.entries(eventsByVenue).map(([venue, events]) => {
                        events.sort((a, b) => new Date(a.start) - new Date(b.start));
                        return { venue, events };
                    });

                    eventListHtml = sortedVenueGroups.map(group => {
                        const itemsHtml = group.events.map(event => {
                            const eventStart = new Date(event.start);
                            const eventEnd = event.end ? new Date(event.end) : null;
                            const timeStr = event.allDay
                                ? 'All Day'
                                : `${eventStart.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })} - ${eventEnd ? eventEnd.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'N/A'}`;

                            return `
                                <div class="mb-2 p-2 border-start border-4 border-success bg-light rounded event-summary-card">
                                    <h6 class="mb-1 fw-semibold">${event.title}</h6>
                                    <small class="text-muted d-block">
                                        <i class="ti ti-clock me-1"></i>${timeStr}
                                    </small>
                                    <small class="text-muted d-block">
                                        <i class="ti ti-users me-1"></i>${event.props.organization_name || 'N/A'}
                                    </small>
                                    <span class="badge bg-label-success mt-1">Approved</span>
                                </div>
                            `;
                        }).join('');

                        return `
                            <div class="mb-3">
                                <div class="d-flex align-items-center mb-2">
                                    <i class="ti ti-map-pin me-2 text-primary"></i>
                                    <h6 class="mb-0 fw-bold text-primary">${group.venue}</h6>
                                </div>
                                ${itemsHtml}
                            </div>
                        `;
                    }).join('');

                } else {
                    eventListHtml = '<div class="alert alert-info mb-0"><i class="ti ti-info-circle me-2"></i>No events scheduled on these date(s).</div>';
                }

                const isMobile = window.innerWidth < 768;

                Swal.fire({
                    icon: 'calendar',
                    title: 'Events on Selected Date(s)',
                    html: `
                        <div class="mb-3">
                            <div class="badge bg-label-primary p-2">
                                <i class="ti ti-calendar me-1"></i>${dateRangeText}
                            </div>
                        </div>
                        <div style="max-height: ${isMobile ? '300px' : '400px'}; overflow-y: auto; text-align: left;">
                            ${eventListHtml}
                        </div>
                    `,
                    width: isMobile ? '95%' : '700px',
                    showCancelButton: true,
                    confirmButtonText: '<i class="ti ti-file-text me-1"></i> Create Permit',
                    cancelButtonText: 'Close',
                    confirmButtonColor: '#5f61e6',
                    cancelButtonColor: '#8592a3'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Redirect to permit form with selected dates
                        const params = new URLSearchParams({
                            date_start: selectedDates.start,
                            date_end: selectedDates.end !== selectedDates.start ? selectedDates.end : ''
                        });
                        window.location.href = '{{ route('permit.form') }}?' + params.toString();
                    }
                });
            }

            // Make calendar responsive
            window.addEventListener('resize', function() {
                calendar.updateSize();
            });
        });
    </script>
@endsection
