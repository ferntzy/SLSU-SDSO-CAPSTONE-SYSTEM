{{-- resources/views/bargo/events/calendar.blade.php --}}
@php $container = 'container-xxl'; @endphp
@extends('layouts.contentNavbarLayout')

@section('title', 'BARGO Event Calendar')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<style>
    .fc-event.bargo { background:#ff851b !important; border-color:#ff851b !important; color:#fff !important; font-weight:600; }
    .fc-event.bargo::before { content:"BARGO "; font-weight:bold; }
    .fc .fc-toolbar-title { color:#ff851b; font-weight:600; }
    .fc .fc-button-primary { background:#ff851b; border-color:#ff851b; }
    .fc .fc-button-primary:hover { background:#e67e22; border-color:#e67e22; }
</style>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
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
        <a href="{{ route('bargo.events.create') }}" class="btn btn-warning shadow-lg btn-lg">
            Add BARGO Event
        </a>
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

    new FullCalendar.Calendar(calendarEl, {
        initialView: 'dayGridMonth',
        selectable: true,
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek,timeGridDay'
        },
        events: '{{ route('bargo.calendar.events') }}',
        eventDidMount: info => {
            if (info.event.extendedProps.is_bargo_event) {
                info.el.classList.add('bargo');
            }
        },
        dateClick: info => {
            window.location = '{{ route('bargo.events.create') }}?date=' + info.dateStr;
        }
    }).render();
});
</script>
@endsection\
