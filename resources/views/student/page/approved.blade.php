{{-- resources/views/student/pages/approved.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Approved Permits')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <nav aria-label="breadcrumb">
        <ol class="breadcrumb breadcrumb-style1">
            <li class="breadcrumb-item"><a href="{{ route('student.dashboard') }}">Dashboard</a></li>
            <li class="breadcrumb-item active text-success">Approved Permits</li>
        </ol>
    </nav>

    <h4 class="fw-bold py-3 mb-4">
        <span class="text-muted fw-light">Permits /</span> Approved
    </h4>

  @php
    $now = \Carbon\Carbon::now();

    $ongoing   = $permits->filter(function($p) use ($now) {
        $start = \Carbon\Carbon::parse($p->date_start);
        if ($p->time_start) {
            $start->setTimeFromTimeString($p->time_start);
        } else {
            $start->startOfDay();
        }

        $end = $p->date_end
            ? \Carbon\Carbon::parse($p->date_end)
            : clone $start;

        if ($p->time_end) {
            $end->setTimeFromTimeString($p->time_end);
        } else {
            $end->endOfDay();
        }

        return $now->between($start, $end, true); // true = inclusive
    });

    $upcoming  = $permits->filter(function($p) use ($now) {
        $start = \Carbon\Carbon::parse($p->date_start);
        if ($p->time_start) {
            $start->setTimeFromTimeString($p->time_start);
        }
        return $now->lt($start);
    });

    $completed = $permits->filter(function($p) use ($now) {
        $end = $p->date_end
            ? \Carbon\Carbon::parse($p->date_end)
            : \Carbon\Carbon::parse($p->date_start);

        if ($p->time_end) {
            $end->setTimeFromTimeString($p->time_end);
        } else {
            $end->endOfDay();
        }

        return $now->gt($end);
    });
@endphp

    {{-- ====================== ONGOING EVENTS ====================== --}}
    @if($ongoing->count() > 0)
    <div class="card border shadow-none mb-4">
        <div class="card-header bg-info bg-opacity-10 border-0 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-info fw-bold">
                Ongoing Events
            </h5>
            <span class="badge bg-info rounded-pill">{{ $ongoing->count() }}</span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-borderless mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Activity</th>
                        <th width="20%">Schedule</th>
                        <th width="18%">Venue</th>
                        <th width="12%">Type</th>
                        <th width="20%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($ongoing as $permit)
                        @php
                            $dateDisplay = \Carbon\Carbon::parse($permit->date_start)->format('M d, Y');
                            if ($permit->date_end && !\Carbon\Carbon::parse($permit->date_end)->isSameDay($permit->date_start)) {
                                $dateDisplay .= ' to ' . \Carbon\Carbon::parse($permit->date_end)->format('M d, Y');
                            }
                            $timeStart = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                            $timeEnd   = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
                            $timeDisplay = $timeStart . ' - ' . $timeEnd;
                        @endphp

                        <tr class="hover-ongoing">
                            <td>
                                <strong class="d-block">{{ Str::limit($permit->title_activity, 50) }}</strong>

                            </td>
                            <td class="small">
                                <i class="mdi mdi-calendar-month text-info me-1"></i>{{ $dateDisplay }}<br>
                                <i class="mdi mdi-clock-outline text-info me-1"></i>{{ $timeDisplay }}
                            </td>
                            <td class="text-muted small">{{ Str::limit($permit->venue, 30) }}</td>
                            <td>
                                <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }} fs-xxs">
                                    {{ $permit->type }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-icon btn-outline-primary btn-sm rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modal{{ $permit->permit_id }}">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <a href="{{ route('student.permit.download', $permit->hashed_id) }}"
                                   class="btn btn-icon btn-info btn-sm rounded-pill ms-1">
                                    <i class="mdi mdi-download"></i>
                                </a>
                            </td>
                        </tr>

                        {{-- Modal --}}
                        <div class="modal fade" id="modal{{ $permit->permit_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow-lg border-0">
                                    <div class="modal-header bg-info bg-opacity-10 border-0">
                                        <h5 class="modal-title fw-bold text-info">
                                            Ongoing: {{ $permit->title_activity }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body pt-3">
                                        <div class="row g-4">
                                            <div class="col-md-6"><strong>Purpose:</strong> {{ $permit->purpose ?? '—' }}</div>
                                            <div class="col-md-6"><strong>Nature:</strong> {{ $permit->nature ?? '—' }}</div>
                                            <div class="col-12"><strong>Venue:</strong> {{ $permit->venue }}</div>
                                            <div class="col-md-6"><strong>Date:</strong> {{ $dateDisplay }}</div>
                                            <div class="col-md-6"><strong>Time:</strong> {{ $timeDisplay }}</div>
                                            <div class="col-md-6"><strong>Participants:</strong> {{ $permit->participants ?? '—' }}</div>
                                            <div class="col-md-6"><strong>Expected Attendees:</strong> {{ $permit->number ?? '—' }} persons</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="{{ route('student.permit.download', $permit->hashed_id) }}" class="btn btn-info">
                                            <i class="mdi mdi-download me-1"></i> Download PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ====================== UPCOMING EVENTS ====================== --}}
    @if($upcoming->count() > 0)
    <div class="card border shadow-none mb-4">
        <div class="card-header bg-success bg-opacity-10 border-0 d-flex align-items-center justify-content-between">
            <h5 class="mb-0 text-success fw-bold">
                Upcoming Events
            </h5>
            <span class="badge bg-success rounded-pill">{{ $upcoming->count() }}</span>
        </div>

        <div class="table-responsive text-nowrap">
            <table class="table table-hover table-borderless mb-0">
                <thead class="table-light">
                    <tr>
                        <th width="30%">Activity</th>
                        <th width="20%">Schedule</th>
                        <th width="18%">Venue</th>
                        <th width="12%">Type</th>
                        <th width="20%" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($upcoming as $permit)
                        @php
                            $dateDisplay = \Carbon\Carbon::parse($permit->date_start)->format('M d, Y');
                            if ($permit->date_end && !\Carbon\Carbon::parse($permit->date_end)->isSameDay($permit->date_start)) {
                                $dateDisplay .= ' to ' . \Carbon\Carbon::parse($permit->date_end)->format('M d, Y');
                            }
                            $timeStart = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                            $timeEnd   = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
                            $timeDisplay = $timeStart . ' - ' . $timeEnd;
                        @endphp

                        <tr class="hover-upcoming">
                            <td>
                                <strong class="d-block">{{ Str::limit($permit->title_activity, 50) }}</strong>

                            </td>
                            <td class="small">
                                <i class="mdi mdi-calendar-month text-success me-1"></i>{{ $dateDisplay }}<br>
                                <i class="mdi mdi-clock-outline text-success me-1"></i>{{ $timeDisplay }}
                            </td>
                            <td class="text-muted small">{{ Str::limit($permit->venue, 30) }}</td>
                            <td>
                                <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }} fs-xxs">
                                    {{ $permit->type }}
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-icon btn-outline-primary btn-sm rounded-pill"
                                        data-bs-toggle="modal" data-bs-target="#modal{{ $permit->permit_id }}">
                                    <i class="mdi mdi-eye"></i>
                                </button>
                                <a href="{{ route('student.permit.download', $permit->hashed_id) }}"
                                   class="btn btn-icon btn-success btn-sm rounded-pill ms-1">
                                    <i class="mdi mdi-download"></i>
                                </a>
                            </td>
                        </tr>

                        <div class="modal fade" id="modal{{ $permit->permit_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                <div class="modal-content shadow-lg border-0">
                                    <div class="modal-header bg-success bg-opacity-10 border-0">
                                        <h5 class="modal-title fw-bold text-success">
                                            Upcoming: {{ $permit->title_activity }}
                                        </h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <div class="modal-body pt-3">
                                        <div class="row g-4">
                                            <div class="col-md-6"><strong>Purpose:</strong> {{ $permit->purpose ?? '—' }}</div>
                                            <div class="col-md-6"><strong>Nature:</strong> {{ $permit->nature ?? '—' }}</div>
                                            <div class="col-12"><strong>Venue:</strong> {{ $permit->venue }}</div>
                                            <div class="col-md-6"><strong>Date:</strong> {{ $dateDisplay }}</div>
                                            <div class="col-md-6"><strong>Time:</strong> {{ $timeDisplay }}</div>
                                            <div class="col-md-6"><strong>Participants:</strong> {{ $permit->participants ?? '—' }}</div>
                                            <div class="col-md-6"><strong>Expected Attendees:</strong> {{ $permit->number ?? '—' }} persons</div>
                                        </div>
                                    </div>
                                    <div class="modal-footer bg-light border-0">
                                        <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                        <a href="{{ route('student.permit.download', $permit->hashed_id) }}" class="btn btn-success">
                                            <i class="mdi mdi-download me-1"></i> Download PDF
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- ====================== COMPLETED EVENTS ====================== --}}
    @if($completed->count() > 0)
    <div class="card border shadow-none">
        <div class="card-header bg-light" style="cursor:pointer" data-bs-toggle="collapse" data-bs-target="#completedTable">
            <div class="d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-muted">
                    Completed Events
                    <span class="badge bg-secondary rounded-pill ms-2">{{ $completed->count() }}</span>
                </h5>
                <i class="mdi mdi-chevron-down text-muted"></i>
            </div>
        </div>

        <div class="collapse show" id="completedTable">
            <div class="table-responsive text-nowrap">
                <table class="table table-hover table-borderless mb-0">
                    <thead class="table-light">
                        <tr>
                            <th width="30%">Activity</th>
                            <th width="20%">Schedule</th>
                            <th width="18%">Venue</th>
                            <th width="12%">Type</th>
                            <th width="20%" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($completed as $permit)
                            @php
                                $dateDisplay = \Carbon\Carbon::parse($permit->date_start)->format('M d, Y');
                                if ($permit->date_end && !\Carbon\Carbon::parse($permit->date_end)->isSameDay($permit->date_start)) {
                                    $dateDisplay .= ' to ' . \Carbon\Carbon::parse($permit->date_end)->format('M d, Y');
                                }
                                $timeStart = $permit->time_start ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A') : '?';
                                $timeEnd   = $permit->time_end ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A') : '?';
                                $timeDisplay = $timeStart . ' - ' . $timeEnd;
                            @endphp

                            <tr class="opacity-75 hover-completed">
                                <td>
                                    <strong class="d-block text-muted">{{ Str::limit($permit->title_activity, 50) }}</strong>

                                </td>
                                <td class="small text-muted">
                                    <i class="mdi mdi-calendar-month me-1"></i>{{ $dateDisplay }}<br>
                                    <i class="mdi mdi-clock-outline me-1"></i>{{ $timeDisplay }}
                                </td>
                                <td class="text-muted small">{{ Str::limit($permit->venue, 30) }}</td>
                                <td>
                                    <span class="badge {{ $permit->type === 'In-Campus' ? 'bg-label-primary' : 'bg-label-info' }} fs-xxs">
                                        {{ $permit->type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <button class="btn btn-icon btn-outline-primary btn-sm rounded-pill"
                                            data-bs-toggle="modal" data-bs-target="#modal{{ $permit->permit_id }}">
                                        <i class="mdi mdi-eye"></i>
                                    </button>
                                    <a href="{{ route('student.permit.download', $permit->hashed_id) }}"
                                       class="btn btn-icon btn-secondary btn-sm rounded-pill ms-1">
                                        <i class="mdi mdi-download"></i>
                                    </a>
                                </td>
                            </tr>

                            <div class="modal fade" id="modal{{ $permit->permit_id }}" tabindex="-1">
                                <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
                                    <div class="modal-content shadow-lg border-0">
                                        <div class="modal-header bg-light border-0">
                                            <h5 class="modal-title text-muted">
                                                Completed: {{ $permit->title_activity }}
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body pt-3">
                                            <div class="row g-4">
                                                <div class="col-md-6"><strong>Purpose:</strong> {{ $permit->purpose ?? '—' }}</div>
                                                <div class="col-md-6"><strong>Nature:</strong> {{ $permit->nature ?? '—' }}</div>
                                                <div class="col-12"><strong>Venue:</strong> {{ $permit->venue }}</div>
                                                <div class="col-md-6"><strong>Date:</strong> {{ $dateDisplay }}</div>
                                                <div class="col-md-6"><strong>Time:</strong> {{ $timeDisplay }}</div>
                                                <div class="col-md-6"><strong>Participants:</strong> {{ $permit->participants ?? '—' }}</div>
                                                <div class="col-md-6"><strong>Expected Attendees:</strong> {{ $permit->number ?? '—' }} persons</div>
                                            </div>
                                        </div>
                                        <div class="modal-footer bg-light border-0">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                            <a href="{{ route('student.permit.download', $permit->hashed_id) }}" class="btn btn-secondary">
                                                <i class="mdi mdi-download me-1"></i> Download PDF
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- ====================== EMPTY STATE ====================== --}}
    @if($permits->total() === 0)
    <div class="card border shadow-none">
        <div class="card-body text-center py-6">
            <i class="mdi mdi-check-circle-outline mdi-48px text-success opacity-30"></i>
            <h5 class="mt-4 text-muted">No Approved Permits Yet</h5>
            <p class="text-muted">Your permits will appear here once fully approved.</p>
            {{-- <a href="{{ route('student.permits.create') }}" class="btn btn-primary">
                Submit New Permit
            </a> --}}
        </div>
    </div>
    @endif
</div>
@endsection

@section('page-style')
<style>
    .hover-ongoing:hover   { background-color: rgba(13, 202, 240, 0.08) !important; }
    .hover-upcoming:hover  { background-color: rgba(34, 197, 94, 0.08) !important; }
    .hover-completed:hover { background-color: rgba(108, 117, 125, 0.08) !important; }
    .table > :not(caption) > * > * { padding: 0.85rem 1rem; }
    .fs-xxs { font-size: 0.65rem; }
</style>
@endsection
