@php
    $container = 'container-xxl';
    // Mock data for demonstration purposes, as this file should not contain the form anymore.
    // Ensure you define the routes 'calendar.events', 'permit.form', and 'calendar.store' in your web.php.
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

    <!-- Event Permit Form Modal (Empty structure to be filled via AJAX) -->
    <div class="modal fade" id="permitModal" tabindex="-1" aria-labelledby="permitModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="permitModalLabel">SDSO Organization Activity Permit</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" style="max-height: 70vh; overflow-y: auto;">
                    <!-- Loading state, replaced by form on success -->
                    <div class="text-center p-5 form-loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Permit Form...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <!-- Submit button added dynamically after form loads -->
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    const permitModalEl = document.getElementById('permitModal');
    const permitModal = new bootstrap.Modal(permitModalEl);
    const modalBody = permitModalEl.querySelector('.modal-body');
    const modalFooter = permitModalEl.querySelector('.modal-footer');
    let selectedDates = null;
    let signaturePadInstance = null;

    // Utility for date formatting
    function formatDate(dateObj, includeTime = false) {
        if (!dateObj) return 'N/A';
        const date = new Date(dateObj);
        return date.toLocaleDateString('en-US', {
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        }) + (includeTime ? ' ' + date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : '');
    }

    // Function to find all events for a given date range
    function getEventsForRange(startStr, endStr) {
        const events = calendar.getEvents();
        const start = new Date(startStr);
        const end = new Date(endStr);
        end.setDate(end.getDate() - 1); // FullCalendar end is exclusive, make it inclusive for filtering

        return events.filter(event => {
            const eventStart = new Date(event.start);
            const eventEnd = event.end ? new Date(event.end) : new Date(event.start);
            // Handle all-day events where end is day + 1
            if (event.allDay && event.end) eventEnd.setDate(eventEnd.getDate() - 1);

            // Check for overlap: event starts before or on range end AND event ends after or on range start
            return eventStart <= end && eventEnd >= start;
        }).map(event => ({
            title: event.title,
            start: event.start,
            end: event.end,
            props: event.extendedProps,
            color: event.backgroundColor || event.color || '#3788d8',
            allDay: event.allDay
        })); // Do NOT sort here; we'll group and sort by time later
    }

    // ----------------------------------------------------
    // 1. Initialize Calendar (MODIFIED)
    // ----------------------------------------------------
    const calendar = new FullCalendar.Calendar(calendarEl, {
        selectable: true,
        selectMirror: true,
        editable: false,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: function(fetchInfo, successCallback, failureCallback) {
             // ... (existing events fetch/mock logic remains the same) ...
             fetch('{{ route('calendar.events') }}')
                .then(response => response.json())
                .then(data => {
                    successCallback(data);
                })
                .catch(error => {
                    console.error('Error fetching events:', error);
                    // Simulating mock data if fetch fails (retain for demo)
                    successCallback([
                        {
                            title: 'Tech Club Meeting (Room 101)',
                            start: '2025-11-25T10:00:00',
                            end: '2025-11-25T12:00:00',
                            extendedProps: { organization_name: 'Tech Club', venue: 'Room 101', purpose: 'Planning next hackathon', status: 'Approved' },
                            color: '#007bff'
                        },
                        {
                            title: 'Drama Rehearsal (Auditorium)',
                            start: '2025-11-28T19:00:00',
                            end: '2025-11-28T21:00:00',
                            extendedProps: { organization_name: 'Drama Society', venue: 'Auditorium', purpose: 'Rehearsal for play', status: 'Pending' },
                            color: '#ffc107'
                        },
                        {
                             title: 'Off-Campus Trip Departure',
                             start: '2025-11-25T08:00:00',
                             end: '2025-11-25T09:00:00',
                             extendedProps: { organization_name: 'Hiking Club', venue: 'School Gate', purpose: 'Departure for hike', status: 'Approved' },
                             color: '#17a2b8'
                        },
                        {
                             title: 'Dance Practice (Gym A)',
                             start: '2025-11-25T17:00:00',
                             end: '2025-11-25T18:30:00',
                             extendedProps: { organization_name: 'Dance Troupe', venue: 'Gym A', purpose: 'Choreography practice', status: 'Approved' },
                             color: '#6f42c1'
                        },
                        {
                            title: 'Multi-Day Retreat (Off-Campus)',
                            start: '2025-12-01',
                            end: '2025-12-04', // Exclusive end date (3 days: 1, 2, 3)
                            allDay: true,
                            extendedProps: { organization_name: 'Student Council', venue: 'Off-Campus Resort', purpose: 'Annual Planning Retreat', status: 'Approved' },
                            color: '#28a745'
                        }
                    ]);
                });
        },

        // Handle date selection (drag or single day click)
        select: function(info) {
            // FullCalendar's 'end' is exclusive. Calculate the actual end date.
            const actualEnd = info.end ? new Date(info.end.getTime() - 86400000).toISOString().split('T')[0] : info.startStr.split('T')[0];

            selectedDates = {
                start: info.startStr.split('T')[0],
                end: actualEnd
            };

            showDateSelectionSummary(selectedDates.start, selectedDates.end);
            calendar.unselect();
        },

        // **NEW HANDLER:** Handle single date click (not on an event)
        dateClick: function(info) {
            // Check if it was an actual click on a day cell (FullCalendar sends a date-only string)
            if (info.dayEl && info.dateStr) {
                 selectedDates = {
                    start: info.dateStr,
                    end: info.dateStr
                };
                showDateSelectionSummary(info.dateStr, info.dateStr);
            }
        },

        // Handle event click to view/delete (existing logic remains)
        eventClick: function (info) {
             // ... (existing eventClick logic remains the same for showing single event details) ...
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
                    <div class="text-start p-3 bg-light rounded shadow-sm">
                        <p class="mb-1"><i class="ti ti-calendar me-2"></i><b>Date(s):</b> ${dateDisplay}</p>
                        <p class="mb-1"><i class="ti ti-clock me-2"></i><b>Time:</b> ${timeDisplay}</p>
                        <p class="mb-1"><i class="ti ti-building me-2"></i><b>Venue:</b> ${props.venue || 'N/A'}</p>
                        <hr class="my-2">
                        <p class="mb-1"><i class="ti ti-users me-2"></i><b>Organization:</b> ${props.organization_name || 'N/A'}</p>
                        <p class="mb-1"><i class="ti ti-clipboard-text me-2"></i><b>Purpose:</b> ${props.purpose || 'N/A'}</p>
                        <span class="badge rounded-pill bg-${props.status === 'Approved' ? 'success' : props.status === 'Pending' ? 'warning' : 'danger'} mt-2">${props.status || 'Status N/A'}</span>
                    </div>
                `,
                showCloseButton: true,
                showConfirmButton: false,
                customClass: {
                    container: 'swal2-container--material',
                    popup: 'swal2-popup--material',
                    title: 'swal2-title--material',
                    htmlContainer: 'swal2-html-container--material'
                }
            });
        }
    });

    calendar.render();

    // ----------------------------------------------------
    // 2. Show Date Selection Summary (HEAVILY MODIFIED)
    // ----------------------------------------------------
    function showDateSelectionSummary(startDateStr, endDateStr) {
        const eventsInSelection = getEventsForRange(startDateStr, endDateStr);

        let dateRangeText = formatDate(startDateStr);
        if (startDateStr !== endDateStr) {
             dateRangeText = `${formatDate(startDateStr)} to ${formatDate(endDateStr)}`;
        }

        // 1. Group events by Venue (The 'Cartesian Plane' approach)
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

             // 2. Sort events within each venue group by start time
            const sortedVenueGroups = Object.entries(eventsByVenue).map(([venue, events]) => {
                events.sort((a, b) => new Date(a.start) - new Date(b.start));
                return { venue, events };
            });

            // 3. Generate HTML using Cards for Venues and List Groups for events
            eventListHtml = sortedVenueGroups.map(group => {
                const itemsHtml = group.events.map(event => {
                    const eventStart = new Date(event.start);
                    const eventEnd = event.end ? new Date(event.end) : null;
                    const timeStr = event.allDay
                        ? 'All Day'
                        : `${eventStart.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true })} - ${eventEnd ? eventEnd.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', hour12: true }) : 'N/A'}`;

                    return `
                        <li class="list-group-item d-flex justify-content-between align-items-center" style="border-left: 5px solid ${event.color};">
                            <div>
                                <h6 class="mb-0 text-dark">${event.title}</h6>
                                <small class="text-muted"><i class="ti ti-clock me-1"></i>${timeStr} | Org: ${event.props.organization_name || 'N/A'}</small>
                            </div>
                            <span class="badge bg-label-${event.props.status === 'Approved' ? 'success' : event.props.status === 'Pending' ? 'warning' : 'danger'}">${event.props.status || 'N/A'}</span>
                        </li>
                    `;
                }).join('');

                return `
                    <div class="card mb-3 shadow-sm border-primary">
                        <div class="card-header bg-primary text-white">
                            <h6 class="mb-0"><i class="ti ti-map-pin me-2"></i>${group.venue}</h6>
                        </div>
                        <ul class="list-group list-group-flush">
                            ${itemsHtml}
                        </ul>
                    </div>
                `;
            }).join('');

        } else {
            eventListHtml = '<div class="alert alert-info mb-0">No events scheduled on these date(s).</div>';
        }

        Swal.fire({
            icon: 'calendar',
            title: 'Selected Date(s) Schedule',
            html: `
                <h5 class="text-primary mb-3">${dateRangeText}</h5>
                <div style="max-height: 400px; overflow-y: auto;">
                    ${eventListHtml}
                </div>
            `,
            width: 700, // Make the modal wider for the venue-based display
            showCancelButton: true,
            confirmButtonText: '<i class="ti ti-file-text me-1"></i> Apply for Permit',
            cancelButtonText: 'Close',
            customClass: {
                container: 'swal2-container--material',
                popup: 'swal2-popup--material',
                title: 'swal2-title--material',
                htmlContainer: 'swal2-html-container--material'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                openPermitForm();
            }
        });
    }

    // ----------------------------------------------------
    // 3. Open Permit Form (AJAX Fetch) - Same as existing
    // 4. Initialize Dynamic Form Components - Same as existing
    // 5. Submit Permit Form (AJAX Post) - Same as existing
    // ----------------------------------------------------
    function openPermitForm() { /* ... existing logic ... */
        // Show loading state
        modalBody.innerHTML = `
            <div class="text-center p-5 form-loading-state">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p class="mt-2">Loading Permit Form...</p>
            </div>
        `;
        // Reset footer to just the cancel button
        modalFooter.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>`;


        // Fetch the form content from the dedicated route
        fetch('{{ route('permit.form') }}')
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.text();
            })
            .then(html => {
                // Update modal body with the fetched form content
                modalBody.innerHTML = html;

                // Update modal footer with the new submit button
                modalFooter.innerHTML = `
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="submitPermitBtn" class="btn btn-primary">Submit Permit</button>
                `;

                // Now that the HTML is loaded, initialize form components
                initializeFormComponents();

                // Pre-fill dates using the selected range
                const formDateStart = document.getElementById('date_start');
                const formDateEnd = document.getElementById('date_end');

                if(formDateStart) formDateStart.value = selectedDates.start;
                // Only pre-fill end date if it's different from the start date
                if(formDateEnd) formDateEnd.value = selectedDates.end !== selectedDates.start ? selectedDates.end : '';

                // Attach the submit handler to the dynamically loaded button
                document.getElementById('submitPermitBtn')?.addEventListener('click', submitPermitForm);

                permitModal.show();
            })
            .catch(error => {
                console.error('Error fetching form:', error);
                modalBody.innerHTML = `<div class="alert alert-danger">Error loading form. Please ensure the 'permit.form' route is working and returning the form HTML.</div>`;
                modalFooter.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;
                permitModal.show(); // Show modal with error
            });
    }

    function initializeFormComponents() { /* ... existing logic ... */

        // Flatpickr for Time
        flatpickr("#time_start", {
            enableTime: true, noCalendar: true, dateFormat: "h:i K", time_24hr: false
        });
        flatpickr("#time_end", {
            enableTime: true, noCalendar: true, dateFormat: "h:i K", time_24hr: false
        });

        // Flatpickr for Dates (Mandatory, even though pre-filled)
        flatpickr("#date_start", {
            dateFormat: "Y-m-d",
            minDate: "today",
            onChange: function(selectedDates, dateStr, instance) {
                const endDateInput = document.getElementById('date_end');
                if (endDateInput && endDateInput._flatpickr) {
                    endDateInput._flatpickr.set('minDate', dateStr);
                }
            }
        });
        flatpickr("#date_end", {
            dateFormat: "Y-m-d",
            minDate: "today"
        });

        // Setup Other fields toggle
        toggleOtherField('nature', 'nature_other_text');
        toggleOtherField('participants', 'participants_other_text');

        // Toggle Venue fields based on selection
        const typeRadios = document.querySelectorAll('input[name="type"]');
        const inCampusGroup = document.getElementById('inCampusVenueGroup');
        const offCampusGroup = document.getElementById('offCampusVenueGroup');

        const updateVenueVisibility = () => {
            const selectedType = document.querySelector('input[name="type"]:checked')?.value;
            if (inCampusGroup) {
               inCampusGroup.style.display = selectedType === 'In-Campus' ? 'block' : 'none';
               inCampusGroup.querySelector('select').required = selectedType === 'In-Campus';
            }
            if (offCampusGroup) {
               offCampusGroup.style.display = selectedType === 'Off-Campus' ? 'block' : 'none';
               offCampusGroup.querySelector('input').required = selectedType === 'Off-Campus';
            }
        };

        typeRadios.forEach(radio => radio.addEventListener('change', updateVenueVisibility));
        updateVenueVisibility(); // Initial call

        // Signature Pad Setup
        const canvas = document.getElementById('signature-pad');
        if (canvas) {
            signaturePadInstance = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black',
                minWidth: 1,
                maxWidth: 3,
            });

            // Ensure canvas is resized to fit container on load
            const resizeObserver = new ResizeObserver(entries => {
                for (let entry of entries) {
                   resizeCanvas(entry.target, signaturePadInstance);
                }
            });
            resizeObserver.observe(canvas.parentElement);

            document.getElementById('clear-signature')?.addEventListener('click', function () {
                signaturePadInstance.clear();
            });
        }
    }

    function resizeCanvas(container, signaturePad) { /* ... existing logic ... */
        const canvas = container.querySelector('canvas');
        if (!canvas) return;

        // Save current signature data before resizing
        const data = signaturePad.toDataURL();

        const ratio = Math.max(window.devicePixelRatio || 1, 1);
        canvas.width = canvas.offsetWidth * ratio;
        canvas.height = canvas.offsetHeight * ratio;
        canvas.getContext('2d').scale(ratio, ratio);

        // Reload the signature data after resizing
        if (data && data !== 'data:image/png;base64,') {
            signaturePad.fromDataURL(data, { ratio });
        } else {
            signaturePad.clear();
        }
    }

    function toggleOtherField(radioName, textFieldId) { /* ... existing logic ... */
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

    function submitPermitForm() { /* ... existing logic ... */
        const form = document.getElementById('permitForm');

        if (!form.checkValidity()) {
            form.reportValidity();
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'Please fill out all required fields.',
                customClass: { container: 'swal2-container--material', popup: 'swal2-popup--material', title: 'swal2-title--material' }
            });
            return;
        }

        const formData = new FormData(form);

        // Handle Nature (Other)
        let natureValue = formData.get('nature');
        if (natureValue === 'Other') {
            natureValue = formData.get('nature_other') || 'Other';
            formData.set('nature', natureValue);
        }

        // Handle Participants (Other)
        let participantsValue = formData.get('participants');
        if (participantsValue === 'Other') {
            participantsValue = formData.get('participants_other') || 'Other';
            formData.set('participants', participantsValue);
        }

        // Handle Venue based on Type and clean up unused fields
        const type = formData.get('type');
        if (type === 'In-Campus') {
            if (!formData.get('venue_id')) {
                Swal.fire('Validation Error', 'Please select a campus venue.', 'warning');
                return;
            }
            formData.delete('venue_other');
        } else if (type === 'Off-Campus') {
            if (!formData.get('venue_other')) {
                Swal.fire('Validation Error', 'Please specify the off-campus venue.', 'warning');
                return;
            }
            formData.delete('venue_id');
        }

        // Signature Validation and Handling
        const signatureUploadInput = document.getElementById('signature_upload');
        let signatureRequired = true;

        if (signaturePadInstance && !signaturePadInstance.isEmpty()) {
            const dataURL = signaturePadInstance.toDataURL('image/png');
            formData.set('signature_data', dataURL); // Send as base64 string
            signatureRequired = false;
        } else if (signatureUploadInput.files.length > 0) {
            signatureRequired = false;
        }

        if (signatureRequired) {
            Swal.fire({
                icon: 'warning',
                title: 'Validation Error',
                text: 'A signature is required (draw or upload).',
                customClass: { container: 'swal2-container--material', popup: 'swal2-popup--material', title: 'swal2-title--material' }
            });
            return;
        }


        // Disable submit button
        const submitBtn = document.getElementById('submitPermitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

        // Submit to server using AJAX
        fetch('{{ route('calendar.store') }}', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                'Accept': 'application/json',
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.message || 'Server error'); });
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                permitModal.hide();
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: data.message,
                    customClass: { container: 'swal2-container--material', popup: 'swal2-popup--material', title: 'swal2-title--material' }
                });
                calendar.refetchEvents(); // Reload calendar events to show the new event
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: data.message || 'Failed to submit permit. Please check your form.',
                    customClass: { container: 'swal2-container--material', popup: 'swal2-popup--material', title: 'swal2-title--material' }
                });
            }
        })
        .catch(error => {
            console.error('Submission Error:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error!',
                text: `An unexpected error occurred: ${error.message || 'Unknown Error'}`,
                customClass: { container: 'swal2-container--material', popup: 'swal2-popup--material', title: 'swal2-title--material' }
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Submit Permit';
        });
    }
});
    </script>
@endsection
