{{-- resources/views/student/permit/pending-list.blade.php --}}
<div class="container-xxl flex-grow-1 container-p-y">
    @if($items->isEmpty())
        <div class="text-center py-6 bg-light rounded-3">
            <i class="bx bx-hourglass bx-lg text-danger opacity-50"></i>
            <h5 class="mt-4 text-muted">No rejected permits</h5>
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
                                            <div class="avatar avatar-sm bg-label-danger rounded-circle flex-shrink-0">
                                                <i class="bx bx-file"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold text-dark">
                                                    {{ Str::limit($permit->title_activity, 40) }}
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
                                        <span class="badge bg-danger text-dark">
                                            Rejected
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
    .hover-lift {
        transition: all 0.2s ease;
    }
    .hover-lift:hover {
        background-color: #fff9e6 !important;
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(255, 159, 67, 0.15) !important;
    }
    .table > tbody > tr:last-child {
        border-bottom: none;
    }
</style>
