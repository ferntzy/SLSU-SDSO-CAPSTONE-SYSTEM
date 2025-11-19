<table class="table table-bordered table-striped align-middle">
  <thead class="table-primary text-center">
    <tr>
      <th>#</th>
      <th>Username</th>
      <th>Action</th>
      <th>IP Address</th>
      <th>User Agent</th>
      <th>Date & Time</th>
    </tr>
  </thead>
  <tbody>
    @foreach ($logs as $index => $log)
      <tr>
        <td class="text-center">{{ $logs->firstItem() + $index }}</td>
        <td>
          @if ($log->user)
            {{ $log->user->username }}
          @else
            <span class="text-muted fst-italic">Unknown User (ID: {{ $log->user_id }})</span>
          @endif
        </td>
        <td>{{ $log->action }}</td>
        <td>{{ $log->ip_address ?? 'N/A' }}</td>
        <td style="max-width: 250px; word-wrap: break-word;">{{ $log->user_agent ?? 'N/A' }}</td>
        <td>{{ \Carbon\Carbon::parse($log->created_at)->timezone('Asia/Manila')->format('M d, Y h:i A') }}</td>
      </tr>
    @endforeach
  </tbody>
</table>

{{-- ✅ Pagination --}}
<div class="d-flex justify-content-center mt-3">
  {{ $logs->links('pagination::bootstrap-5') }}
</div>