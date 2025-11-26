<h3 class="mb-4">
    <i class="bx bx-play-circle text-primary me-2"></i>
    Ongoing Events ({{ $items->count() }})
</h3>

@if($items->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bx bx-time-five bx-lg opacity-50"></i>
        <p>No events currently ongoing.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($items as $permit)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-primary shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">{{ $permit->title_activity }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="bx bx-calendar"></i>
                            {{ \Carbon\Carbon::parse($permit->date_start)->format('M d, Y') }}
                            @if($permit->date_end)
                                → {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}
                            @endif
                        </p>
                        <span class="badge bg-primary mb-3">Happening Now</span>
                        <div class="d-flex justify-content-end">
                            <a href="{{ route('student.permit.view', $permit->hashed_id) }}"
                               class="btn btn-sm btn-outline-primary">
                                <i class="bx bx-show"></i> View Details
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
