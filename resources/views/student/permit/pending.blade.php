{{-- resources/views/student/permit/pending.blade.php --}}
<h3 class="mb-4">
    <i class="bx bx-time-five text-warning me-2"></i>
    Pending Permits ({{ $items->count() }})
</h3>

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bx bx-hourglass bx-lg text-warning"></i>
        <p class="text-muted mt-3">No pending permits at the moment.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($items as $permit)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-warning shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">{{ $permit->title_activity }}</h6>
                        <p class="card-text text-muted small">
                            <i class="bx bx-calendar"></i>
                            {{ $permit->date_start?->format('M d, Y') ?? 'Date not set' }}
                            @if($permit->date_end)
                                – {{ $permit->date_end->format('M d, Y') }}
                            @endif
                        </p>
                        <p class="mb-2">
                            <span class="badge bg-warning">Awaiting Approval</span>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-muted">
                                Submitted {{ $permit->created_at->diffForHumans() }}
                            </small>
                            <a href="{{ route('student.permit.view', $permit->hashed_id) }}"
                               class="btn btn-sm btn-outline-warning">
                                <i class="bx bx-file-find"></i> View
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
