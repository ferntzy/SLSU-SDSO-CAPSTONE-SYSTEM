{{-- resources/views/bargo/events/approved.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Approved Permits - BARGO')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-success text-white" style="background: linear-gradient(135deg, #51cf66 0%, #40c057 100%);">
                <div class="card-body">
                    <h4 class="text-white mb-0">
                        <i class="mdi mdi-check-decagram me-2"></i>
                        Permits Approved by BARGO
                    </h4>
                    <p class="mb-0 opacity-75">These have been forwarded to SDSO Head</p>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($approvedReviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Organization</th>
                                <th>Event</th>
                                <th>Approved On</th>
                                <th>View</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($approvedReviews as $flow)
                                @php $permit = $flow->permit; @endphp
                                <tr>
                                    <td><strong>{{ $permit->organization->acronym }}</strong></td>
                                    <td>{{ Str::limit($permit->title_activity, 50) }}</td>
                                    <td>{{ $flow->approved_at?->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <a href="{{ route('bargo.permit.pdf', $permit->hashed_id) }}" target="_blank"
                                           class="btn btn-sm btn-success">
                                            <i class="mdi mdi-file-pdf-box"></i> View Signed PDF
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="mdi mdi-thumb-up-outline text-success" style="font-size: 4rem;"></i>
                    <h5>No approved permits yet</h5>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
