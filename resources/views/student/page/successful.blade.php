{{-- resources/views/student/page/successful.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Successful Events')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Successful & Completed Events</h4>
            <p class="text-muted mb-0">Submit post-event documentation: minutes, photos, reports</p>
        </div>
        <span class="badge bg-label-success fs-5">
            <i class="mdi mdi-trophy-outline"></i> {{ $successfulEvents->total() }} Completed
        </span>
    </div>

    <div class="card">
        <div class="card-datatable table-responsive">
            <table class="table table-hover">
                <thead class="table-light">
                    <tr>
                        <th>#</th>
                        <th>Event Title</th>
                        <th>Type</th>
                        <th>Dates</th>
                        <th>Participants</th>
                        <th>Files</th>
                        <th>Status</th>
                        <th class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($successfulEvents as $index => $permit)
                        <tr>
                            <td>{{ $successfulEvents->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <div>
                                        <div class="fw-semibold">{{ Str::limit($permit->title_activity, 40) }}</div>
                                        <small class="text-muted">Permit #{{ $permit->permit_id }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge bg-label-primary">{{ ucfirst($permit->type ?? 'Event') }}</span>
                            </td>
                            <td class="text-nowrap small">
                                {{ $permit->date_start->format('M d') }}
                                @if($permit->date_end && !$permit->date_end->isSameDay($permit->date_start))
                                    – {{ $permit->date_end->format('M d, Y') }}
                                @else
                                    , {{ $permit->date_start->format('Y') }}
                                @endif
                            </td>
                            <td class="text-muted">{{ $permit->participants ?? '—' }}</td>
                            <td>
                                @if($permit->reports->count())
                                    <a href="{{ route('student.reports.show',Crypt::encryptString($permit->permit_id)) }}"
                                       class="btn btn-sm btn-outline-success">
                                        <i class="menu-icon tf-icons ti ti-files"></i>
                                     {{ $permit->reports->count() }} file{{ $permit->reports->count() > 1 ? 's' : '' }}
                                    </a>
                                @else
                                    <span class="text-muted">No files</span>
                                @endif
                            </td>
                            <td>
                                @if($permit->is_completed)
                                    <span class="badge bg-success">
                                        <i class="mdi mdi-check-decagram"></i> Submitted
                                    </span>
                                @else
                                    <span class="badge bg-warning text-dark">
                                        <i class="mdi mdi-clock-outline"></i> Pending
                                    </span>
                                @endif
                            </td>
                            <td class="text-center">
                                <div class="dropdown d-inline">
                                    <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown">
                                        <i class="mdi mdi-dots-vertical"></i>
                                    </button>
                                    <div class="dropdown-menu">

                                        @if(!$permit->is_completed)
                                        <a class="dropdown-item" href="javascript:void(0);"
                                           data-bs-toggle="modal"
                                           data-bs-target="#uploadModal{{ $permit->permit_id }}">
                                            <i class="mdi mdi-cloud-upload-outline me-1"></i> Upload Files
                                        </a>
                                        @endif
                                        <!-- Submit to SDSO (only if files exist & not submitted) -->
                                        @if($permit->reports->count() > 0 && !$permit->is_completed)
                                            <a class="dropdown-item text-success" href="javascript:void(0);"
                                               data-bs-toggle="modal"
                                               data-bs-target="#submitModal{{ $permit->permit_id }}">
                                                <i class="mdi mdi-send-check-outline me-1"></i> Submit to SDSO
                                            </a>
                                        @endif

                                        <!-- View Files -->
                                        @if($permit->reports->count())
                                            <a class="dropdown-item" href="{{ route('student.reports.show', Crypt::encryptString($permit->permit_id)) }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View Files
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Upload Modal --}}
                        <div class="modal fade" id="uploadModal{{ $permit->permit_id }}">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="hidden" name="event_id" value="{{ $permit->permit_id }}">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">
                                                <i class="mdi mdi-cloud-upload-outline"></i>
                                                Upload Documentation
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="alert alert-info">
                                                <i class="mdi mdi-information-outline"></i>
                                                Upload minutes, photos, reports, certificates, etc.
                                            </div>
                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label>Category</label>
                                                    <select name="document_type" class="form-select" required>
                                                        <option value="minutes">Minutes of the Meeting</option>
                                                        <option value="photos">Event Photos</option>
                                                        <option value="report">Post-Event Report</option>
                                                        <option value="certificate">Certificate</option>
                                                        <option value="other">Other Document</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Title (Optional)</label>
                                                    <input type="text" name="title" class="form-control">
                                                </div>
                                                <div class="col-md-6">
                                                    <label>Description</label>
                                                    <textarea name="description" rows="2" class="form-control"></textarea>
                                                </div>
                                                <div class="col-12">
                                                    <label>Select Files</label>
                                                    <input type="file" name="documents[]" multiple required class="form-control">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">
                                                <i class="mdi mdi-upload"></i> Upload Files
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        {{-- Submit to SDSO Modal --}}
                        <!-- Submit to SDSO Modal -->
<!-- Submit to SDSO Modal -->
<!-- Submit to SDSO Modal – IRREVERSIBLE WARNING -->
<div class="modal fade" id="submitModal{{ $permit->permit_id }}" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('student.permit.submit', Crypt::encryptString($permit->permit_id)) }}" method="POST">
            @csrf
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-body text-center py-5 px-4">
                    <!-- Big Warning Icon -->
                    <div class="mb-4">
                        <i class="mdi mdi-alert-circle-outline text-warning" style="font-size: 5rem;"></i>
                    </div>

                    <h3 class="mb-3 fw-bold text-dark">Final Submission to SDSO</h3>

                    <p class="text-muted mb-4">
                        You are about to submit documentation for:<br>
                        <strong class="text-primary">{{ $permit->title_activity }}</strong>
                    </p>

                    <div class="alert alert-danger border-0 rounded-3 mb-4">
                        <i class="mdi mdi-alert-decagram text-danger me-2"></i>
                        <strong>This action is IRREVERSIBLE!</strong><br>
                        Once submitted:
                        <ul class="list-unstyled mt-2 mb-0 text-start small">
                            <li>You <strong>cannot add or remove</strong> any files</li>
                            <li>You <strong>cannot edit</strong> titles or descriptions</li>

                        </ul>
                    </div>

                    <p class="mb-4">
                        You have uploaded <strong class="text-success">{{ $permit->reports->count() }} file(s)</strong><br>
                        <span class="text-danger fw-bold">Are you absolutely sure?</span>
                    </p>

                    <!-- Action Buttons -->
                    <div class="d-flex gap-3 justify-content-center">
                        <button type="button" class="btn btn-label-secondary btn-lg px-4" data-bs-dismiss="modal">
                            <i class="mdi mdi-arrow-left"></i> Cancel
                        </button>
                        <button type="submit" class="btn btn-success btn-lg px-5">
                            <i class="mdi mdi-send-lock-outline"></i>
                            Yes, Submit Permanently
                        </button>
                    </div>

                    <!-- Extra Confirmation Text -->
                    <small class="text-muted d-block mt-4">
                        This action cannot be undone.
                    </small>
                </div>
            </div>
        </form>
    </div>
</div>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6">
                                <i class="mdi mdi-trophy-outline text-success" style="font-size: 3.5rem;"></i>
                                <h5 class="mt-3">No Completed Events</h5>
                                <p class="text-muted">Events will appear here after they are marked as done.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">
            {{ $successfulEvents->links() }}
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

{{-- Show Swal if session has it --}}
@if(session('swal'))
<script>
    Swal.fire({
        title: "{{ session('swal.title') }}",
        text: "{{ session('swal.text') }}",
        icon: "{{ session('swal.icon') }}",
        timer: {{ session('swal.timer') ?? 'null' }},
        showConfirmButton: false,
        allowOutsideClick: false
    });
</script>
@endif
@endsection
