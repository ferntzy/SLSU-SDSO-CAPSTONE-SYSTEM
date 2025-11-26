{{-- @if ($items->isEmpty())
    <div class="alert alert-danger">No canceled events.</div>
@else
    @foreach ($items as $event)
        @include('student.permits.permit-card', ['permit' => $event])
    @endforeach
@endif --}}
