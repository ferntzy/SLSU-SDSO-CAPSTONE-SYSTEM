{{-- resources/views/student/page/ongoing.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Ongoing Events')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Ongoing Events</h4>
            <p class="text-muted mb-0">Events currently in progress</p>
        </div>
        <div>
            <span class="badge bg-label-info fs-5">
                <i class="ti ti-player-play me-1"></i>
                {{ $ongoingEvents->total() }} Active
            </span>
        </div>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th width="30">#</th>
                        <th>Activity Title</th>
                        <th>Type</th>
                        <th>Venue</th>
                        <th>Date & Time</th>
                        <th>Participants</th>
                        <th>Status</th>

                    </tr>
                </thead>
                <tbody>
                    @forelse($ongoingEvents as $index => $permit)
                        <tr>
                            <td>{{ $ongoingEvents->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">

                                    <div>
                                        <strong>{{ Str::limit($permit->title_activity, 50) }}</strong>
                                        <small class="text-muted d-block">
                                            Permit #{{ $permit->permit_id }}
                                        </small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">
                                    {{ ucfirst($permit->type ?? 'N/A') }}
                                </span>
                            </td>
                            <td>{{ $permit->venue ?? '—' }}</td>
                            <td>
                                <div class="text-nowrap">
                                    <div>
                                        <i class="ti ti-calendar-event text-info me-1"></i>
                                        {{ $permit->date_start?->format('M d, Y') }}
                                    </div>
                                    @if($permit->time_start && $permit->time_end)
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($permit->time_start)->format('g:i A') }}
                                            –
                                            {{ \Carbon\Carbon::parse($permit->time_end)->format('g:i A') }}
                                        </small>
                                    @endif
                                </div>
                            </td>
                           <td>
    @if($permit->participants)
        <strong class="text-success">{{ $permit->participants }}</strong>
    @else
        <span class="text-muted">—</span>
    @endif
</td>
                            <td>
                                <span class="badge bg-label-info">
                                    <i class="ti ti-player-play me-1"></i>Ongoing
                                </span>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5">
                                <i class="ti ti-player-play-off ti-lg text-muted mb-3"></i>
                                <h5>No Ongoing Events</h5>
                                <p class="text-muted">Your approved events will appear here when they start.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="card-footer">
            {{ $ongoingEvents->onEachSide(1)->links() }}
        </div>
    </div>
</div>
@endsection
