{{-- resources/views/bargo/calendar.blade.php --}}
@php $container = 'container-xxl'; @endphp
@extends('layouts.contentNavbarLayout')

@section('title', 'BARGO Event Calendar')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<style>
    .fc-event.bargo { background:#ff851b !important; border-color:#ff851b !important; color:white !important; font-weight:600; }
    .fc-event.bargo::before { content:"BARGO "; font-weight:bold; }
    .swal2-popup { font-family: 'Public Sans', sans-serif; }
    .form-label { font-weight: 600; color: #5f61e6; }
    .text-bargo { color: #ff851b !important; }
</style>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">BARGO Event Calendar</h4>
            <p class="text-muted mb-0">
                All approved events • <strong class="text-warning">Orange = BARGO Events</strong>
            </p>
        </div>
        <button class="btn btn-warning shadow-lg" onclick="addEvent()">
            Add BARGO Event
        </button>
    </div>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            <div id="calendar"></div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendarEl = document.getElementById('calendar');
    let selectedDates = { start: null, end: null };
    let calendar;

    calendar = new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        headerToolbar: { left: 'prev,next today', center: 'title', right: 'dayGridMonth,timeGridWeek,timeGridDay' },
        events: '{{ route('bargo.calendar.events') }}',
        eventDidMount: info => info.event.extendedProps.is_bargo_event && info.el.classList.add('bargo'),
        select: info => {
            const end = new Date(info.end); end.setDate(end.getDate() - 1);
            selectedDates = { start: info.startStr.split('T')[0], end: end.toISOString().split('T')[0] };
            addEvent();
        },
        dateClick: info => {
            selectedDates = { start: info.dateStr, end: info.dateStr };
            addEvent();
        },
        eventClick: info => info.event.extendedProps.is_bargo_event ? editEvent(info.event) : viewEvent(info.event)
    });
    calendar.render();

    window.addEvent = function() {
        const today = new Date().toISOString().split('T')[0];
        const start = selectedDates.start || today;
        const end = selectedDates.end || start;

        Swal.fire({
            title: '<strong class="text-bargo">Create BARGO Event</strong>',
            html: `
                <form id="bargoForm" class="text-start">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Title of Activity <span class="text-danger">*</span></label>
                            <input type="text" name="title_activity" class="form-control" placeholder="e.g. BARGO General Assembly 2025" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Purpose <span class="text-danger">*</span></label>
                            <textarea name="purpose" class="form-control" rows="3" placeholder="State the objective of the activity" required></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Nature of Activity <span class="text-danger">*</span></label>
                            <select name="nature" class="form-select" required>
                                <option value="">-- Select Nature --</option>
                                <option value="Training/Seminar">Training/Seminar</option>
                                <option value="Conference/Summit">Conference/Summit</option>
                                <option value="Meeting">Meeting</option>
                                <option value="Program">Program</option>
                                <option value="Competition">Competition</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-md-6" id="natureOther" style="display:none;">
                            <label class="form-label">Specify Other</label>
                            <input type="text" name="nature_other_text" class="form-control" placeholder="e.g. Team Building">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Venue <span class="text-danger">*</span></label>
                            <select name="venue" class="form-select" required>
                                <option value="">-- Select Venue --</option>
                                @foreach(\App\Models\Venue::orderBy('venue_name')->get() as $venue)
                                    <option value="{{ $venue->venue_id }}">{{ $venue->venue_name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Participants <span class="text-danger">*</span></label>
                           <input type="text" name="participants" class="form-control" placeholder="e.g. SNHS Students">
                        </div>
                        <div class="col-12" id="participantsOther" style="display:none;">
                            <input type="text" name="participants_other_text" class="form-control" placeholder="Specify participants">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Expected Number of Participants</label>
                            <input type="number" name="number" class="form-control" min="1" value="50">
                        </div>

                        <div class="col-12">
                            <div class="alert alert-success mt-3">
                                <strong>Note:</strong> This event will be <strong>automatically approved</strong> and visible to all users immediately.
                            </div>
                        </div>

                        <input type="hidden" name="date_start" value="${start}">
                        <input type="hidden" name="date_end" value="${end}">
                        <input type="hidden" name="type" value="In-Campus">
                    </div>
                </form>
            `,
            width: '800px',
            showCancelButton: true,
            confirmButtonText: 'Create & Approve Event',
            confirmButtonColor: '#ff851b',
            didOpen: () => {
                // Show/hide "Other" fields
                const natureSelect = document.querySelector('[name="nature"]');
                const participantsSelect = document.querySelector('[name="participants"]');

                natureSelect?.addEventListener('change', function() {
                    document.getElementById('natureOther').style.display = this.value === 'Other' ? 'block' : 'none';
                });

                participantsSelect?.addEventListener('change', function() {
                    document.getElementById('participantsOther').style.display = this.value === 'Other' ? 'block' : 'none';
                });
            },
            preConfirm: () => {
                const form = document.getElementById('bargoForm');
                const data = new FormData(form);
                const title = data.get('title_activity')?.trim();
                const purpose = data.get('purpose')?.trim();
                const venue = data.get('venue');

                if (!title || !purpose || !venue) {
                    Swal.showValidationMessage('Please fill all required fields');
                    return false;
                }

                return Object.fromEntries(data.entries());
            }
        }).then(result => {
            if (result.isConfirmed) {
                Swal.fire({ title: 'Creating Event...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });

                fetch('{{ route('bargo.calendar.store') }}', {
                    method: 'POST',
                    body: new URLSearchParams(result.value),
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                })
                .then(r => r.json())
                .then(d => {
                    if (d.success) {
                        calendar.refetchEvents();
                        Swal.fire({
                            icon: 'success',
                            title: 'BARGO Event Created!',
                            text: 'Auto-approved and now visible to everyone',
                            timer: 3000,
                            showConfirmButton: false
                        });
                    }
                })
                .catch(() => {
                    Swal.fire('Error', 'Failed to create event', 'error');
                });
            }
        });
    };
});
</script>
@endsection
