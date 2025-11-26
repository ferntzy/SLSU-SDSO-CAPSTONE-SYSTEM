{{-- resources/views/student/permit/approved.blade.php --}}
<h3 class="mb-4">
    <i class="bx bx-check-circle text-success me-2"></i>
    Approved Permits ({{ $items->count() }})
</h3>

@if($items->isEmpty())
    <div class="text-center py-5">
        <i class="bx bx-check-double bx-lg text-success opacity-50"></i>
        <p class="text-muted mt-3">No approved permits yet.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($items as $permit)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">{{ $permit->title_activity }}</h6>
                        <p class="card-text text-muted small">
                            <i class="bx bx-calendar-check"></i>
                            {{ $permit->date_start?->format('M d, Y') }}
                            @if($permit->date_end)
                                – {{ $permit->date_end->format('M d, Y') }}
                            @endif
                        </p>
                        <p class="mb-2">
                            <span class="badge bg-success">Approved</span>
                        </p>
                        <div class="d-flex justify-content-between align-items-center mt-3">
                            <small class="text-success">
                                Approved {{ $permit->approvals->where('status', 'approved')->last()?->approved_at?->diffForHumans() }}
                            </small>
                            <a href="{{ route('student.permit.view', $permit->hashed_id) }}"
                               class="btn btn-sm btn-success">
                                <i class="bx bx-download"></i> PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
