{{-- resources/views/adviser/history.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'My Approval History')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Breadcrumb & Back Button --}}
    <div class="d-flex align-items-center mb-4">
        <a href="{{ route('adviser.dashboard') }}" class="btn btn-sm btn-icon btn-label-secondary me-3">
            <i class="mdi mdi-arrow-left"></i>
        </a>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item">
                    <a href="{{ route('adviser.dashboard') }}">Dashboard</a>
                </li>
                <li class="breadcrumb-item active">Approval History</li>
            </ol>
        </nav>
    </div>

    {{-- Header Section --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-2">
                <i class="mdi mdi-history text-primary me-2"></i>My Approval History
            </h4>
            <p class="text-muted mb-0">All permits you have reviewed as Faculty Adviser</p>
        </div>
        <div class="d-flex gap-2">
            <div class="badge bg-label-success px-3 py-2">
                <i class="mdi mdi-check-circle me-1"></i>
                <strong>{{ $history->where('status', 'approved')->count() }}</strong> Approved
            </div>

        </div>
    </div>
    {{-- Main Card --}}
    <div class="card border-0 shadow-sm">
        @if($history->count() > 0)
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr class="border-bottom">
                            <th class="ps-4 py-3">
                                <small class="text-muted fw-semibold text-uppercase">Date & Time</small>
                            </th>
                            <th class="py-3">
                                <small class="text-muted fw-semibold text-uppercase">Organization</small>
                            </th>
                            <th class="py-3">
                                <small class="text-muted fw-semibold text-uppercase">Event Details</small>
                            </th>
                            <th class="py-3">
                                <small class="text-muted fw-semibold text-uppercase">Type</small>
                            </th>
                            <th class="py-3">
                                <small class="text-muted fw-semibold text-uppercase">approved</small>
                            </th>

                            <th class="text-center py-3 pe-4">
                                <small class="text-muted fw-semibold text-uppercase">Actions</small>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-heading">{{ $item->updated_at->format('M d, Y') }}</span>
                                        <small class="text-muted">{{ $item->updated_at->format('h:i A') }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar avatar-sm flex-shrink-0 me-3">

                                        </div>
                                        <div>
                                            <h6 class="mb-0">{{ $item->permit->organization->acronym ?? Str::limit($item->permit->organization->organization_name, 20) }}</h6>
                                            <small class="text-muted d-block text-truncate" style="max-width: 150px;">
                                                {{ $item->permit->organization->organization_name }}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <h6 class="mb-0 fw-semibold">{{ Str::limit($item->permit->title_activity, 35) }}</h6>

                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-info">{{ $item->permit->type ?? 'Event' }}</span>
                                </td>
                                <td>
                                    @if($item->status === 'approved')
                                        <span class="badge ">
                                            <i class="mdi mdi-check-bold text-success me-1"></i>
                                        </span>
                                    @endif
                                </td>


<td class="text-center pe-4">
    <a href="{{ route('adviser.permit.pdf', $item->permit->hashed_id) }}"
       target="_blank"
       class="btn btn-sm btn-icon btn-label-primary"
       title="Download PDF">
        <i class="mdi mdi-download mdi-24px"></i>
    </a>
</td>

                            </tr>

                            {{-- Comments Modal --}}
                            @if($item->comments)
                                <div class="modal fade" id="commentsModal{{ $item->approval_id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">
                                                    <i class="mdi mdi-message-square-detail text-primary me-2"></i>Comments
                                                </h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small mb-2">Event</label>
                                                    <p class="fw-semibold mb-0">{{ $item->permit->title_activity }}</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small mb-2">approved</label>
                                                    <p class="mb-0">
                                                        @if($item->status === 'approved')
                                                            <span class="badge bg-success">Approved</span>
                                                        @else
                                                            <span class="badge bg-danger">Rejected</span>
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label text-muted small mb-2">Date</label>
                                                    <p class="mb-0">{{ $item->updated_at->format('F d, Y h:i A') }}</p>
                                                </div>
                                                <div>
                                                    <label class="form-label text-muted small mb-2">Your Comments</label>
                                                    <div class="alert alert-primary mb-0">
                                                        {{ $item->comments }}
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Modern Pagination --}}
            @if($history->hasPages())
                <div class="card-footer border-top bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            Showing <strong>{{ $history->firstItem() }}</strong> to <strong>{{ $history->lastItem() }}</strong> of <strong>{{ $history->total() }}</strong> entries
                        </div>
                        <nav aria-label="Page navigation">
                            <ul class="pagination pagination-sm mb-0">
                                {{-- Previous Button --}}
                                @if ($history->onFirstPage())
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="mdi mdi-chevron-left"></i>
                                        </span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $history->previousPageUrl() }}">
                                            <i class="mdi mdi-chevron-left"></i>
                                        </a>
                                    </li>
                                @endif

                                {{-- Page Numbers --}}
                                @foreach(range(1, $history->lastPage()) as $page)
                                    @if($page == $history->currentPage())
                                        <li class="page-item active">
                                            <span class="page-link">{{ $page }}</span>
                                        </li>
                                    @elseif($page == 1 || $page == $history->lastPage() || abs($page - $history->currentPage()) <= 2)
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $history->url($page) }}">{{ $page }}</a>
                                        </li>
                                    @elseif($page == 2 || $page == $history->lastPage() - 1)
                                        <li class="page-item disabled">
                                            <span class="page-link">...</span>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($history->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link" href="{{ $history->nextPageUrl() }}">
                                            <i class="mdi mdi-chevron-right"></i>
                                        </a>
                                    </li>
                                @else
                                    <li class="page-item disabled">
                                        <span class="page-link">
                                            <i class="mdi mdi-chevron-right"></i>
                                        </span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif

        @else
            {{-- Empty State --}}
            <div class="card-body text-center py-5">
                <div class="avatar avatar-xl mx-auto mb-4">
                    <span class="avatar-initial rounded-circle bg-label-primary">
                       <i class="mdi mdi-history mdi-24px"></i>
                    </span>
                </div>
                <h5 class="mb-2">No Approval History Yet</h5>
                <p class="text-muted mb-4">When you approve or reject permits, they will appear here.</p>
                <a href="{{ route('adviser.dashboard') }}" class="btn btn-primary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back to Dashboard
                </a>
            </div>
        @endif
    </div>

</div>

<style>
/* Materio-Pro Clean Styling */
.card {
    transition: box-shadow 0.3s ease;
}

.table-hover tbody tr {
    transition: background-color 0.2s ease;
}

.table-hover tbody tr:hover {
    background-color: rgba(105, 108, 255, 0.04);
    cursor: pointer;
}

.avatar-initial {
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.badge {
    font-weight: 500;
    padding: 0.375rem 0.75rem;
}

.page-link {
    border: none;
    color: #697a8d;
    transition: all 0.2s ease;
}

.page-link:hover {
    background-color: rgba(105, 108, 255, 0.08);
    color: #696cff;
}

.page-item.active .page-link {
    background-color: #696cff;
    color: white;
    box-shadow: 0 2px 4px rgba(105, 108, 255, 0.4);
}

.page-item.disabled .page-link {
    background-color: transparent;
    color: #c7cdd4;
}

.pagination-sm .page-link {
    padding: 0.375rem 0.75rem;
    font-size: 0.875rem;
    border-radius: 0.375rem;
    margin: 0 0.125rem;
}

.card-footer {
    padding: 1rem 1.5rem;
}

.btn-icon {
    width: 2rem;
    height: 2rem;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.text-heading {
    color: #566a7f;
}

.shadow-sm {
    box-shadow: 0 2px 6px 0 rgba(67, 89, 113, 0.12) !important;
}

/* Modal Improvements */
.modal-header {
    border-bottom: 1px solid rgba(67, 89, 113, 0.1);
    padding: 1.5rem;
}

.modal-footer {
    border-top: 1px solid rgba(67, 89, 113, 0.1);
    padding: 1rem 1.5rem;
}

/* Smooth Animations */
.modal.fade .modal-dialog {
    transition: transform 0.3s ease-out;
}

.btn {
    transition: all 0.2s ease;
}

.btn:hover {
    transform: translateY(-1px);
}

.btn-icon:hover {
    transform: translateY(-2px);
}

/* Breadcrumb Styling */
.breadcrumb-style1 {
    margin-bottom: 0;
}

.breadcrumb-style1 .breadcrumb-item + .breadcrumb-item::before {
    color: #c7cdd4;
}

/* Badge Enhancements */
.badge.bg-label-success {
    background-color: rgba(113, 221, 55, 0.16) !important;
    color: #71dd37 !important;
}

.badge.bg-label-danger {
    background-color: rgba(255, 62, 29, 0.16) !important;
    color: #ff3e1d !important;
}

.badge.bg-label-info {
    background-color: rgba(3, 195, 236, 0.16) !important;
    color: #03c3ec !important;
}

.badge.bg-label-primary {
    background-color: rgba(105, 108, 255, 0.16) !important;
    color: #696cff !important;
}

.badge.bg-label-secondary {
    background-color: rgba(133, 146, 163, 0.16) !important;
    color: #8592a3 !important;
}

/* Table Border Improvements */
thead {
    background-color: rgba(67, 89, 113, 0.04);
}

.table > :not(caption) > * > * {
    padding: 1rem 0.75rem;
    border-bottom-width: 1px;
    border-color: rgba(67, 89, 113, 0.1);
}
</style>
@endsection
