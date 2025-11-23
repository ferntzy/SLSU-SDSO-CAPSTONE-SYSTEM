@if ($items->isEmpty())
    <div class="alert alert-warning">No pending permits.</div>
@else
    @foreach ($items as $permit)
        @include('student.permits.permit-card', ['permit' => $permit])
    @endforeach
@endif
