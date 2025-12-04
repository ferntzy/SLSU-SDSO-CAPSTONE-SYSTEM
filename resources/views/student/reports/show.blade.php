{{-- resources/views/student/reports/show.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Documentation – ' . $permit->title_activity)

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">
                <i class="mdi mdi-folder-multiple-image"></i>
                Submitted Documentation
            </h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ route('successful') }}">Successful Events</a></li>
                    <li class="breadcrumb-item active">{{ Str::limit($permit->title_activity, 35) }}</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('successful') }}" class="btn btn-label-secondary">
            <i class="mdi mdi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Event Summary -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row align-items-center">
                <div class="col-lg-8">
                    <h5 class="mb-1">{{ $permit->title_activity }}</h5>
                    <p class="text-muted small mb-0">
                        <i class="mdi mdi-calendar-range"></i>
                        {{ $permit->date_start->format('M d, Y') }}
                        @if($permit->date_end && !$permit->date_end->isSameDay($permit->date_start))
                            – {{ $permit->date_end->format('M d, Y') }}
                        @endif
                        <span class="mx-2">•</span>
                        <i class="mdi mdi-account-group"></i> {{ $permit->participants ?? '—' }}
                    </p>
                </div>
                <div class="col-lg-4 text-lg-end mt-3 mt-lg-0">
                    @if($permit->is_completed)
                        <span class="badge bg-success fs-6 px-4 py-2">
                            <i class="mdi mdi-check-decagram"></i> Submitted to SDSO
                        </span>
                    @else
                        <span class="badge bg-warning text-dark">
                            <i class="mdi mdi-clock-outline"></i> Pending Submission
                        </span>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Files Section -->
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="mdi mdi-folder-multiple-outline"></i>
                Uploaded Files ({{ $permit->reports->count() }})
            </h5>

            <!-- HIDE "Add More Files" BUTTON WHEN SUBMITTED -->  
        </div>

        <div class="card-body">
            @if($permit->reports->count() > 0)

                <!-- Photos Gallery -->
                @if($permit->reports->whereIn('mime_type', ['image/jpeg','image/jpg','image/png','image/gif','image/webp'])->count() > 0)
                    <h6 class="mt-4 mb-3 text-primary">
                        <i class="mdi mdi-image-multiple-outline"></i> Event Photos
                    </h6>
                    <div class="row g-3 mb-5">
                        @foreach($permit->reports->whereIn('mime_type', ['image/jpeg','image/jpg','image/png','image/gif','image/webp']) as $photo)
                            <div class="col-6 col-sm-4 col-lg-3">
                                <a href="{{ Storage::url($photo->document_url) }}" class="glightbox">
                                    <img src="{{ Storage::url($photo->document_url) }}"
                                         class="img-fluid rounded shadow-sm"
                                         alt="{{ $photo->title ?? 'Photo' }}"
                                         style="height: 180px; width: 100%; object-fit: cover;">
                                    <div class="mt-2 small text-center text-muted">
                                        {{ $photo->title ?? 'Photo' }}
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>
                @endif

                <!-- Documents Table (Auto-detect file type) -->
                @if($permit->reports->whereNotIn('mime_type', ['image/jpeg','image/jpg','image/png','image/gif','image/webp'])->count() > 0)
                    <h6 class="mt-4 mb-3 text-info">
                        <i class="mdi mdi-file-document-multiple-outline"></i> Documents & Reports
                    </h6>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>File</th>
                                    <th>Type</th>
                                    <th>Title / Notes</th>
                                    <th>Uploaded</th>
                                    <th class="text-center">View</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($permit->reports->whereNotIn('mime_type', ['image/jpeg','image/jpg','image/png','image/gif','image/webp']) as $doc)
                                    <tr>
                                        <td>
                                            @php
                                                $ext = strtolower(pathinfo($doc->original_filename, PATHINFO_EXTENSION));
                                                $icon = match($ext) {
                                                    'pdf' => 'mdi-file-pdf-box text-danger',
                                                    'doc','docx' => 'mdi-file-word-box text-primary',
                                                    'xls','xlsx' => 'mdi-file-excel-box text-success',
                                                    'ppt','pptx' => 'mdi-file-powerpoint-box text-warning',
                                                    default => 'mdi-file-document-outline text-secondary'
                                                };
                                            @endphp
                                            <i class="mdi {{ $icon }} mdi-24px me-2"></i>
                                            {{ Str::limit($doc->original_filename, 30) }}
                                        </td>
                                        <td>
                                            <span class="badge bg-label-info">
                                                {{ ucfirst(str_replace('_', ' ', $doc->document_type)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $doc->title ?? '—' }}</strong>
                                            @if($doc->description)
                                                <br><small class="text-muted">{{ Str::limit($doc->description, 60) }}</small>
                                            @endif
                                        </td>
                                        <td class="text-nowrap small">
                                            {{ $doc->created_at->format('M d, Y') }}
                                            <br><span class="text-muted">{{ $doc->created_at->format('g:i A') }}</span>
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ Storage::url($doc->document_url) }}" target="_blank"
                                               class="btn btn-icon btn-sm rounded-pill btn-outline-primary">
                                                <i class="mdi mdi-eye-outline"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

            @else
                <div class="text-center py-6">
                    <i class="mdi mdi-folder-off-outline mdi-48px text-muted"></i>
                    <h6 class="mt-3">No files uploaded yet</h6>
                    <p class="text-muted">Upload minutes, photos, or reports to get started.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Upload Modal (Only shows if not submitted) -->
    @if(!$permit->is_completed)
    <div class="modal fade" id="uploadModal">
        <div class="modal-dialog modal-lg">
            <form action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="event_id" value="{{ $permit->permit_id }}">
                <div class="modal-content">
                    <div class="modal-header">
                                            <h5 class="modal-title">
                            <i class="mdi mdi-cloud-upload-outline"></i>
                            Upload More Files
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-12">
                                <label class="form-label">File Category</label>
                                <select name="document_type" class="form-select" required>
                                    <option value="minutes">Minutes of the Meeting</option>
                                    <option value="photos">Event Photos</option>
                                    <option value="report">Post-Event Report</option>
                                    <option value="certificate">Certificate / Attendance</option>
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
                                <label>Files (Multiple)</label>
                                <input type="file" name="documents[]" multiple required class="form-control">
                                <div class="form-text">PDF, Images, Word, Excel accepted</div>
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
    @endif
</div>

<!-- GLightbox -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/glightbox/dist/css/glightbox.min.css">
<script src="https://cdn.jsdelivr.net/npm/glightbox/dist/js/glightbox.min.js"></script>
<script>
    GLightbox({ selector: '.glightbox' });
</script>
@endsection
