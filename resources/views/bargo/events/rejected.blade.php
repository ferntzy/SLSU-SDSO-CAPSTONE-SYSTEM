{{-- resources/views/bargo/events/rejected.blade.php --}}
@extends('layouts.contentNavbarLayout')

@section('title', 'Rejected Permits - BARGO')

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">
    <div class="row mb-4">
        <div class="col-12">
            <div class="card bg-danger text-white" style="background: linear-gradient(135deg, #ff6b6b 0%, #ee5a52 100%);">
                <div class="card-body">
                    <h4 class="text-white mb-0">
                        <i class="mdi mdi-close-circle-outline me-2"></i>
                        Permits Rejected by BARGO
                    </h4>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            @if($rejectedReviews->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-light">
                            <tr>
                                <th>Organization</th>
                                <th>Event</th>
                                <th>Rejected On</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rejectedReviews as $flow)
                                @php $permit = $flow->permit; @endphp
                                <tr>
                                    <td><strong>{{ $permit->organization->acronym }}</strong></td>
                                    <td>{{ $permit->title_activity }}</td>
                                    <td>{{ $flow->updated_at->format('M d, Y h:i A') }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-danger" data-bs-toggle="tooltip" title="{{ $flow->comments }}">
                                            <i class="mdi mdi-comment-text-outline"></i> View Reason
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="mdi mdi-emoticon-happy-outline text-success" style="font-size: 4rem;"></i>
                    <h5>No rejections</h5>
                    <p class="text-muted">Great job! All permits passed your review.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
