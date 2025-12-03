{{-- resources/views/adviser/event-calendar.blade.php --}}
@php $container = 'container-xxl'; @endphp

@extends('layouts.adviserLayout')

@section('title', 'Campus Event Calendar')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<style>
    .fc { font-family: 'Public Sans', sans-serif; }

    /* Toolbar — your original look, now fully responsive */
    .fc .fc-toolbar {
        flex-wrap: wrap;
        gap: 0.5rem;
        padding: 1rem !important;
        background: #fff;
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

    /* Critical Fix: Uniform cell height on ALL screens */
    .fc .fc-daygrid-day-frame {
        min-height: 120px !important;
        height: 100% !important;
        display: flex;
        flex-direction: column;
    }
    .fc .fc-daygrid-day-top { flex-shrink: 0; }
    .fc .fc-daygrid-body,
    .fc .fc-daygrid-day-bg,
    .fc-scrollgrid-sync-table {
        width: 100% !important;
    }
    .fc .fc-scrollgrid-section-body td,
    .fc .fc-scrollgrid-section-liquid td {
        height: 100% !important;
    }

    /* Your exact original green events */
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

    /* Today highlight — your original style */
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

    /* Day hover */
    .fc .fc-daygrid-day:hover {
        background: rgba(95, 97, 230, 0.08);
        border-radius: 12px;
        cursor: pointer;
    }

    /* Your original modal event item */
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

    /* Mobile perfection */
    @media (max-width: 768px) {
        .fc .fc-toolbar { padding: 0.75rem !important; }
        .fc .fc-toolbar-title { font-size: 1.25rem !important; }
        .fc .fc-button { padding: 0.4rem 0.7rem !important; font-size: 0.85rem !important; }
        .fc-event { font-size: 0.72rem !important; padding: 4px 6px !important; }
        .fc .fc-daygrid-day-frame { min-height: 90px !important; }
    }
</style>
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.17/index.global.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">
    <div class="text-center mb-5 px-3">
        <h2 class="fw-bold text-primary mb-2">Campus Event Calendar</h2>
        <p class="text-muted fs-6">
            All events approved by VP-SAS
        </p>
    </div>

    <div class="card border-0 shadow-lg overflow-hidden">
        <div class="card-body p-0">
            <div id="calendar"></div>
        </div>
    </div>
</div>

@php
    use Illuminate\Support\Facades\DB;
    use Carbon\Carbon;

    $events = DB::table('permits as p')
        ->join('organizations as o', 'p.organization_id', '=', 'o.organization_id')
        ->join('event_approval_flow as eaf', 'p.permit_id', '=', 'eaf.permit_id')
        ->where('eaf.approver_role', 'VP_SAS')
        ->where('eaf.status', 'approved')
        ->select('p.title_activity as title','p.date_start as start','p.date_end as end',
                 'p.time_start','p.time_end','p.venue','p.purpose','o.organization_name')
        ->distinct()
        ->get();

    $calendarEvents = $events->map(function($e) {
        $allDay = is_null($e->time_start) && is_null($e->time_end);
        $end = $e->end ? Carbon::parse($e->end)->addDay()->format('Y-m-d') : null;

        return [
            'title' => $e->title ?? 'Untitled Event',
            'start' => $e->start,
            'end'   => $end,
            'allDay' => $allDay,
            'extendedProps' => [
                'venue' => $e->venue ?? 'Not specified',
                'purpose' => $e->purpose,
                'organization_name' => $e->organization_name,
                'time' => $allDay ? 'All Day' : trim(($e->time_start ?? '') . ($e->time_end ? ' – '.$e->time_end : ''))
            ]
        ];
    })->all();
@endphp

<script>
document.addEventListener('DOMContentLoaded', function () {
    const calendar = new FullCalendar.Calendar(document.getElementById('calendar'), {
        initialView: 'dayGridMonth',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'dayGridMonth,timeGridWeek'
        },
        height: 'auto',
        dayMaxEvents: 3,
        eventDisplay: 'block',
        events: @json($calendarEvents),

        dateClick: function(info) {
            const clicked = info.dateStr;
            const events = calendar.getEvents().filter(e => {
                const s = e.startStr.split('T')[0];
                const end = e.endStr ? e.endStr.split('T')[0] : s;
                return clicked >= s && clicked < end;
            });

            const niceDate = info.date.toLocaleDateString('en-US', {
                weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
            });

            if (events.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'No Events',
                    html: `<p class="mb-0">No approved events on <strong>${niceDate}</strong></p>`,
                    confirmButtonColor: '#5f61e6'
                });
                return;
            }

            let html = '';
            events.forEach(e => {
                const p = e.extendedProps;
                html += `
                <div class="event-item p-3 mb-3">
                    <div class="d-flex justify-content-between align-items-start mb-2">
                        <h6 class="fw-bold text-success mb-0">${e.title}</h6>
                        <span class="badge bg-success small">Approved</span>
                    </div>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6"><strong>Time:</strong> ${p.time}</div>
                        <div class="col-12 col-sm-6"><strong>Venue:</strong> ${p.venue}</div>
                        <div class="col-12"><strong>Org:</strong> <span class="badge bg-primary small">${p.organization_name}</span></div>
                        ${p.purpose ? `<div class="col-12 mt-2"><div class="bg-white p-3 rounded small text-muted border">${p.purpose}</div></div>` : ''}
                    </div>
                </div>`;
            });

            Swal.fire({
                title: `<div class="text-center"><div class="text-primary fs-4 fw-bold">${events.length} Event${events.length>1?'s':''}</div><small class="text-muted">${niceDate}</small></div>`,
                html: `<div class="px-2" style="max-height:70vh; overflow-y:auto;">${html}</div>`,
                width: window.innerWidth < 768 ? '95%' : '800px',
                showCloseButton: true,
                showConfirmButton: false
            });
        }
    });

    calendar.render();

    // Force perfect layout on resize
    window.addEventListener('resize', () => {
        setTimeout(() => calendar.updateSize(), 100);
    });
});
</script>
@endsection
