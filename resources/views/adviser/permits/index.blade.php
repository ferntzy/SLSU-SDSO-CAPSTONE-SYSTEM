{{-- resources/views/adviser/permits/index.blade.php --}}
@extends('layouts.adviserLayout')

@section('title', 'All Permits')

@section('content')
<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h5>All Permit Requests</h5>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Organization</th>
                        <th>Activity</th>
                        <th>Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($permits as $p)
                    <tr>
                        <td>{{ $p->organization_name}}</td>
                        <td>{{ Str::limit($p->title_activity, 40) }}</td>
                        <td>{{ Carbon\Carbon::parse($p->date_start)->format('M d, Y') }}</td>
                        <td>
                            @if($p->approvalFlow()->where('approver_role', 'Faculty_Adviser')->first()?->status === 'pending')
                                <span class="badge bg-warning">Pending Your Approval</span>
                            @else
                                <span class="badge bg-secondary">{{ $p->approvalFlow()->where('approver_role', 'Faculty_Adviser')->first()?->status ?? 'Unknown' }}</span>
                            @endif
                        </td>
                        <td>
                            <a href="/adviser/permits/review/{{ $p->permit_id }}" class="btn btn-sm btn-primary">
                                Review
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-4 text-muted">No permits found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $permits->links() }}
    </div>
</div>
@endsection
