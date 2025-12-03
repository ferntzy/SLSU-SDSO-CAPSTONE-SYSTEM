{{-- resources/views/student/page/submissions-history.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Submissions History')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    {{-- Search Box --}}
    <div class="card mb-3 border-0 shadow-sm">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('student.submissions.history') }}">
                <div class="input-group input-group-merge">
                    <span class="input-group-text border-0 bg-transparent">Search</span>
                    <input type="text" name="search" class="form-control border-0"
                           placeholder="Search by title, venue, or type..."
                           value="{{ request('search') }}" autofocus>
                    @if(request('search'))
                        <a href="{{ route('student.submissions.history') }}" class="input-group-text text-muted">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    @php
        use Illuminate\Support\Facades\DB;
        use Illuminate\Pagination\LengthAwarePaginator;
        use Carbon\Carbon;

        // ──────────────────────────────────────────────────────────────
        // Always define $permits first — this is the fix!
        // ──────────────────────────────────────────────────────────────
        $permits = new LengthAwarePaginator([], 0, 20); // dummy paginator

        $profileId = auth()->user()->profile_id ?? null;
        $organizationId = $profileId
            ? DB::table('members')->where('profile_id', $profileId)->value('organization_id')
            : null;

        if ($organizationId) {
            $query = DB::table('permits')->where('organization_id', $organizationId);

            // Apply search
            if ($search = request('search')) {
                $query->where(function($q) use ($search) {
                    $q->where('title_activity', 'LIKE', "%{$search}%")
                      ->orWhere('venue', 'LIKE', "%{$search}%")
                      ->orWhere('type', 'LIKE', "%{$search}%");
                });
            }

            $perPage     = 20;
            $currentPage = LengthAwarePaginator::resolveCurrentPage();
            $items       = $query->orderByDesc('created_at')
                                 ->skip(($currentPage - 1) * $perPage)
                                 ->take($perPage + 1)
                                 ->get();

            $total = $query->count();

            $permits = new LengthAwarePaginator($items, $total, $perPage, $currentPage, [
                'path'     => LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => 'page',
            ]);

            $permits->appends(request()->query());
        }
        // If no organization → $permits stays as empty paginator (no errors!)
    @endphp

    <div class="d-flex align-items-center justify-content-between mb-4">
        <h5 class="mb-0 text-muted">Permit Submissions History</h5>
        <small class="text-muted">{{ $permits->total() }} total</small>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">

            @if($permits->isEmpty())
                <div class="text-center py-6">
                    <i class="ti ti-file-off ti-48px text-muted opacity-50 mb-3"></i>
                    <p class="text-muted mb-2">
                        @if(request('search'))
                            No permits found for "<strong>{{ request('search') }}</strong>"
                        @else
                            No permit submissions yet
                        @endif
                    </p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-hover table-borderless mb-0">
                        <thead class="bg-light border-bottom">
                            <tr class="text-muted small text-uppercase">
                                <th class="ps-4 fw-medium" style="width:50px">#</th>
                                <th class="fw-medium">Activity Title</th>
                                <th class="fw-medium">Type</th>
                                <th class="fw-medium">Date</th>
                                <th class="fw-medium">Venue</th>
                                <th class="fw-medium">Status</th>
                                <th class="fw-medium text-end pe-4">Submitted</th>
                            </tr>
                        </thead>
                        <tbody class="text-muted">
                            @foreach($permits as $i => $p)
                                @php
                                    $isApproved = DB::table('event_approval_flow')
                                        ->where('permit_id', $p->permit_id)
                                        ->where('approver_role', 'VP_SAS')
                                        ->where('status', 'approved')
                                        ->exists();

                                    $isRejected = DB::table('event_approval_flow')
                                        ->where('permit_id', $p->permit_id)
                                        ->where('status', 'rejected')
                                        ->exists();

                                    $status = $isRejected ? 'rejected' : ($isApproved ? 'approved' : 'pending');

                                    $date = $p->date_start
                                        ? Carbon::parse($p->date_start)->format('M j')
                                        : '—';
                                    if ($p->date_end && $p->date_end != $p->date_start) {
                                        $date .= ' → ' . Carbon::parse($p->date_end)->format('M j');
                                    }
                                @endphp
                                <tr class="border-bottom">
                                    <td class="ps-4 small">{{ $permits->firstItem() + $i }}</td>
                                    <td class="text-dark">
                                        <div class="text-truncate" style="max-width:300px;">
                                            {{ $p->title_activity ?? 'Untitled Activity' }}
                                        </div>
                                    </td>
                                    <td><span class="badge bg-light text-muted border small">{{ $p->type ?? '—' }}</span></td>
                                    <td class="small text-nowrap">{{ $date }}</td>
                                    <td class="small text-truncate" style="max-width:150px;">{{ $p->venue ?? '—' }}</td>
                                    <td>
                                        @if($status === 'approved')
                                            <span class="badge bg-light text-success border small">Approved</span>
                                        @elseif($status === 'rejected')
                                            <span class="badge bg-light text-danger border small">Rejected</span>
                                        @else
                                            <span class="badge bg-light text-secondary border small">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4 small text-muted">
                                        {{ Carbon::parse($p->created_at)->format('M j, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="px-4 py-3 bg-white border-top d-flex align-items-center justify-content-between">
                    <div class="text-muted small">
                        Showing {{ $permits->firstItem() }}–{{ $permits->lastItem() }} of {{ $permits->total() }}
                    </div>
                    {{ $permits->withQueryString()->links('pagination::bootstrap-5') }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('page-style')
<style>
    .table tbody tr:hover { background-color: #f8f9fa !important; }
    .badge { font-weight: 500; }
    .pagination .page-link { border:none; color:#6c757d; }
    .pagination .page-item.active .page-link { background:#e9ecef; border-color:#dee2e6; color:#495057; }
</style>
@endsection
