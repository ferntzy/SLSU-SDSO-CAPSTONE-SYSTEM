@if ($items->isEmpty())
    <div class="alert alert-success">No successfully finished events.</div>
@else
    @foreach ($items as $event)
        @include('student.permits.permit-card', ['permit' => $event])
    @endforeach
@endif
