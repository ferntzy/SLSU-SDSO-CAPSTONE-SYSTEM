@if ($items->isEmpty())
    <div class="alert alert-info">No ongoing events right now.</div>
@else
    @foreach ($items as $event)
        @include('student.permits.permit-card', ['permit' => $event])
    @endforeach
@endif
