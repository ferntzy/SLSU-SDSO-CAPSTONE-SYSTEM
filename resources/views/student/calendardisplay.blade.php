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
                    <div class="text-center p-5 form-loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Permit Form...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
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
                    color: event.backgroundColor || event.color || '#3788d8',
                    allDay: event.allDay
                }));
            }

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
                    fetch('{{ route('calendar.events') }}')
                        .then(response => response.json())
                        .then(data => {
                            successCallback(data);
                        })
                        .catch(error => {
                            console.error('Error fetching events:', error);
                            failureCallback(error);
                        });
                },

                select: function(info) {
                    const actualEnd = info.end ? new Date(info.end.getTime() - 86400000).toISOString().split('T')[0] : info.startStr.split('T')[0];

                    selectedDates = {
                        start: info.startStr.split('T')[0],
                        end: actualEnd
                    };

                    showDateSelectionSummary(selectedDates.start, selectedDates.end);
                    calendar.unselect();
                },

                dateClick: function(info) {
                    if (info.dayEl && info.dateStr) {
                        selectedDates = {
                            start: info.dateStr,
                            end: info.dateStr
                        };
                        showDateSelectionSummary(info.dateStr, info.dateStr);
                    }
                },

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

            function showDateSelectionSummary(startDateStr, endDateStr) {
                const eventsInSelection = getEventsForRange(startDateStr, endDateStr);

                let dateRangeText = formatDate(startDateStr);
                if (startDateStr !== endDateStr) {
                    dateRangeText = `${formatDate(startDateStr)} to ${formatDate(endDateStr)}`;
                }

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
                    width: 700,
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

            function openPermitForm() {
                modalBody.innerHTML = `
                    <div class="text-center p-5 form-loading-state">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Loading Permit Form...</p>
                    </div>
                `;
                modalFooter.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>`;

                fetch('{{ route('permit.form') }}')
                    .then(response => {
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.text();
                    })
                    .then(html => {
                        modalBody.innerHTML = html;

                        modalFooter.innerHTML = `
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="button" id="submitPermitBtn" class="btn btn-primary">Submit Permit</button>
                        `;

                        initializeFormComponents();

                        const formDateStart = document.getElementById('date_start');
                        const formDateEnd = document.getElementById('date_end');

                        if(formDateStart) formDateStart.value = selectedDates.start;
                        if(formDateEnd) formDateEnd.value = selectedDates.end !== selectedDates.start ? selectedDates.end : '';

                        document.getElementById('submitPermitBtn')?.addEventListener('click', submitPermitForm);

                        permitModal.show();
                    })
                    .catch(error => {
                        console.error('Error fetching form:', error);
                        modalBody.innerHTML = `<div class="alert alert-danger">Error loading form. Please ensure the 'permit.form' route is working and returning the form HTML.</div>`;
                        modalFooter.innerHTML = `<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>`;
                        permitModal.show();
                    });
            }

            function initializeFormComponents() {
                flatpickr("#time_start", {
                    enableTime: true, noCalendar: true, dateFormat: "h:i K", time_24hr: false
                });
                flatpickr("#time_end", {
                    enableTime: true, noCalendar: true, dateFormat: "h:i K", time_24hr: false
                });

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

                toggleOtherField('nature', 'nature_other_text');
                toggleOtherField('participants', 'participants_other_text');

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
                updateVenueVisibility();

                const canvas = document.getElementById('signature-pad');
                if (canvas) {
                    signaturePadInstance = new SignaturePad(canvas, {
                        backgroundColor: 'rgba(255, 255, 255, 0)',
                        penColor: 'black',
                        minWidth: 1,
                        maxWidth: 3,
                    });

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

            function resizeCanvas(container, signaturePad) {
                const canvas = container.querySelector('canvas');
                if (!canvas) return;

                const data = signaturePad.toDataURL();

                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);

                if (data && data !== 'data:image/png;base64,') {
                    signaturePad.fromDataURL(data, { ratio });
                } else {
                    signaturePad.clear();
                }
            }

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

            function submitPermitForm() {
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

                let natureValue = formData.get('nature');
                if (natureValue === 'Other') {
                    natureValue = formData.get('nature_other') || 'Other';
                    formData.set('nature', natureValue);
                }

                let participantsValue = formData.get('participants');
                if (participantsValue === 'Other') {
                    participantsValue = formData.get('participants_other') || 'Other';
                    formData.set('participants', participantsValue);
                }

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

                const signatureUploadInput = document.getElementById('signature_upload');
                let signatureRequired = true;

                if (signaturePadInstance && !signaturePadInstance.isEmpty()) {
                    const dataURL = signaturePadInstance.toDataURL('image/png');
                    formData.set('signature_data', dataURL);
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

                const submitBtn = document.getElementById('submitPermitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';

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
                        calendar.refetchEvents();
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
