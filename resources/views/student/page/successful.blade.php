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
                                    <a href="{{ route('student.reports.show', Crypt::encryptString($permit->permit_id)) }}"
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

                                        @if($permit->reports->count() > 0 && !$permit->is_completed)
                                            <a class="dropdown-item text-success" href="javascript:void(0);"
                                               data-bs-toggle="modal"
                                               data-bs-target="#submitModal{{ $permit->permit_id }}">
                                                <i class="mdi mdi-send-check-outline me-1"></i> Submit to SDSO
                                            </a>
                                        @endif

                                        @if($permit->reports->count())
                                            <a class="dropdown-item" href="{{ route('student.reports.show', Crypt::encryptString($permit->permit_id)) }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View Files
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- ONLY ONE UPLOAD MODAL - FIXED ID & FULLY WORKING --}}
                        <div class="modal fade" id="uploadModal{{ $permit->permit_id }}" tabindex="-1">
                            <div class="modal-dialog modal-lg">
                                <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                   <input type="hidden" name="permit_id" value="{{ $permit->permit_id }}">
                                   <input type="hidden" name="event_id" value="{{ $permit->permit_id }}">

                                    <div class="modal-content">
                                        <div class="modal-header bg-primary text-white">
                                            <h5 class="modal-title">
                                                <i class="mdi mdi-cloud-upload-outline"></i>
                                                Upload Documentation – {{ Str::limit($permit->title_activity, 30) }}
                                            </h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>

                                        <div class="modal-body">
                                            <div class="alert alert-info small">
                                                <i class="mdi mdi-information-outline"></i>
                                                Upload minutes, photos, reports, certificates, etc. Multiple files allowed.
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">Category <span class="text-danger">*</span></label>
                                                    <select name="document_type" class="form-select" required>
                                                        <option value="">-- Select Type --</option>
                                                        <option value="minutes">Minutes of Meeting</option>
                                                        <option value="photos">Event Photos</option>
                                                        <option value="report">Post-Event Report</option>
                                                        <option value="certificate">Certificate</option>
                                                        <option value="other">Other Document</option>
                                                    </select>
                                                </div>

                                                <div class="col-md-6">
                                                    <label class="form-label">Title (Optional)</label>
                                                    <input type="text" name="title" class="form-control" placeholder="e.g. Attendance Sheet">
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Description (Optional)</label>
                                                    <textarea name="description" rows="3" class="form-control" placeholder="Add notes here..."></textarea>
                                                </div>

                                                <div class="col-12">
                                                    <label class="form-label">Select Files <span class="text-danger">*</span></label>
                                                    <input type="file" name="documents[]" multiple required class="form-control"
                                                           accept=".pdf,.jpg,.jpeg,.png,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                                    <small class="text-muted">Max 15MB per file • Multiple files allowed</small>
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
                        <div class="modal fade" id="submitModal{{ $permit->permit_id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <form action="{{ route('student.permit.submit', Crypt::encryptString($permit->permit_id)) }}" method="POST">
                                    @csrf
                                    <div class="modal-content border-0 shadow-lg">
                                        <div class="modal-body text-center py-5">
                                            <i class="mdi mdi-alert-circle-outline text-warning mb-4" style="font-size: 4.5rem;"></i>
                                            <h4 class="fw-bold">Final Submission</h4>
                                            <p class="text-muted">You are about to submit documentation for:</p>
                                            <h5 class="text-primary fw-bold">{{ $permit->title_activity }}</h5>

                                            <div class="alert alert-danger mt-3">
                                                <strong>This action is IRREVERSIBLE!</strong><br>
                                                After submission:
                                                <ul class="text-start mt-2 mb-0">
                                                    <li>You cannot add or remove files</li>
                                                    <li>You cannot edit anything</li>
                                                </ul>
                                            </div>

                                            <p>You have uploaded <strong class="text-success">{{ $permit->reports->count() }}</strong> file(s)</p>

                                            <div class="d-flex gap-3 justify-content-center mt-4">
                                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">
                                                    <i class="mdi mdi-send-check"></i> Submit Permanently
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-6">
                                <div class="mb-3">
                                    <i class="mdi mdi-trophy-outline text-success" style="font-size: 4rem;"></i>
                                </div>
                                <h5>No Completed Events Yet</h5>
                                <p class="text-muted">Events will appear here after they are marked as successful.</p>
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
{{-- SweetAlert2 - CENTERED MODAL (Default Style) --}}
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@if(session('swal'))
<script>
    Swal.fire({
        title: "{{ session('swal.title') }}",
        text: "{{ session('swal.text') }}",
        icon: "{{ session('swal.icon') }}",
        timer: {{ session('swal.timer') ?? 3000 }},
        timerProgressBar: true,
        showConfirmButton: false,
        allowOutsideClick: false,
        allowEscapeKey: false,
        // Removed toast + position → now shows in the CENTER like a normal modal
    });
</script>
@endif
@endsection
