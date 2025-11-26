{{-- resources/views/student/permit/pending-list.blade.php --}}
<div class="container-xxl flex-grow-1 container-p-y">
    @if($items->isEmpty())
        <div class="text-center py-6 bg-light rounded-3">
            <i class="bx bx-hourglass bx-lg text-success opacity-50"></i>
            <h5 class="mt-4 text-muted">No Approved permits</h5>
            <p class="text-muted mb-0">You're all caught up!</p>
        </div>
    @else
        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-4">Activity</th>
                                <th>Date</th>
                                <th>Time</th>
                                <th>Venue</th>
                                <th>Submitted</th>
                                <th class="text-end pe-4">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $permit)
                                @php
                                    $start = $permit->time_start
                                        ? \Carbon\Carbon::parse($permit->time_start)->format('g:i A')
                                        : '—';
                                    $end = $permit->time_end
                                        ? \Carbon\Carbon::parse($permit->time_end)->format('g:i A')
                                        : '—';
                                @endphp
                                <tr class="hover-lift">
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="avatar avatar-sm bg-label-success rounded-circle flex-shrink-0">
                                                <i class="bx bx-file"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">
                                                    {{ Str::limit($permit->title_activity, 10) }}
                                                </div>
                                                <small class="text-muted">{{ $permit->type ?? 'Permit' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="text-nowrap">
                                            <strong>{{ $permit->date_start?->format('M d') }}</strong>
                                            <small class="text-muted d-block">
                                                {{ $permit->date_start?->format('Y') }}
                                            </small>
                                            @if($permit->date_end)
                                                <small class="text-muted">
                                                    → {{ $permit->date_end->format('M d, Y') }}
                                                </small>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="fw-medium">{{ $start }} @if($end !== '—') – {{ $end }} @endif</span>
                                    </td>
                                    <td>
                                        <span class="text-truncate d-inline-block" style="max-width: 130px;">
                                            {{ $permit->venue ?? '—' }}
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            {{ $permit->created_at->diffForHumans() }}
                                        </small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <span class="badge bg-success text-dark">
                                            Approved
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Optional: Add pagination if $items is paginated -->
        @if(method_exists($items, 'links'))
            <div class="mt-4">
                {{ $items->links('pagination::bootstrap-5') }}
            </div>
        @endif
    @endif
</div>

<style>
    /* Hover effect with Materio success palette */
    .hover-lift {
        transition: all 0.25s ease;
    }
    .hover-lift:hover {
        background-color: #e8f8e4 !important; /* Materio light-success */
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(113, 221, 55, 0.25) !important; /* Materio success shadow */
    }

    /* Pagination green (Materio success) */
    .pagination .page-item.active .page-link {
        background-color: #71dd37 !important;
        border-color: #71dd37 !important;
        color: #fff !important;
    }

    /* Avatar / green dot */
    .avatar.bg-label-success {
        background-color: #e8f8e4 !important;
        color: #71dd37 !important;
    }

    /* Badge success override */
    .badge.bg-success {
        background-color: #71dd37 !important;
        color: #fff !important;
    }
</style>

