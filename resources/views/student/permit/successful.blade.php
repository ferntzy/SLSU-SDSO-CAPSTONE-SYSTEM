<h3 class="mb-4">
    <i class="bx bx-trophy text-success me-2"></i>
    Successful Events ({{ $items->count() }})
</h3>

@if($items->isEmpty())
    <div class="text-center py-5 text-muted">
        <i class="bx bx-check-double bx-lg opacity-50"></i>
        <p>No completed events yet.</p>
    </div>
@else
    <div class="row g-4">
        @foreach($items as $permit)
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card border-success shadow-sm h-100">
                    <div class="card-body">
                        <h6 class="card-title fw-bold">{{ $permit->title_activity }}</h6>
                        <p class="text-muted small mb-2">
                            <i class="bx bx-calendar-check"></i>
                            Ended {{ \Carbon\Carbon::parse($permit->date_end)->format('M d, Y') }}
                        </p>
                        <span class="badge bg-success mb-3">Successfully Completed</span>
                        <div class="text-end">
                            <small class="text-success">
                                {{ \Carbon\Carbon::parse($permit->date_end)->diffForHumans() }}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif
