@if ($items->isEmpty())
    <div class="alert alert-success">No approved permits yet.</div>
@else
    @foreach ($items as $permit)
        @include('student.permits.permit-card', ['permit' => $permit])
    @endforeach
@endif
