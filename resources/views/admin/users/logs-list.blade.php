@if ($logs->isEmpty())
  <div class="alert alert-info text-center m-3">No user logs found.</div>
@else

<table class="table table-hover">
  <thead>
    <tr>
      <th>No.</th>
      <th>Username</th>
      <th>Action</th>
      <th>IP Address</th>
      <th>User Agent</th>
      <th>Date & Time</th>
    </tr>
  </thead>

  <tbody id="logsBody" class="table-border-bottom-0">
    @foreach ($logs as $index => $log)
      <tr>
        <!-- Use correct numbering with pagination -->
        <td class="text-center">{{ ($logs->currentPage()-1) * $logs->perPage() + $index + 1 }}</td>

        <td>
          @if ($log->user)
            {{ $log->user->username }}
          @else
            <span class="text-muted fst-italic">Unknown User (ID: {{ $log->user_id }})</span>
          @endif
        </td>

        <td>{{ $log->action }}</td>
        <td>{{ $log->ip_address ?? 'N/A' }}</td>

        <td style="max-width: 250px; word-wrap: break-word;">
          {{ $log->user_agent ?? 'N/A' }}
        </td>

        <td>
          {{ \Carbon\Carbon::parse($log->created_at)
                 ->timezone('Asia/Manila')
                 ->format('M d, Y  -  h:i A') }}
        </td>
      </tr>
    @endforeach
  </tbody>
</table>

<!-- FIXED: Laravel Pagination Links -->
<div class="d-flex justify-content-center mt-3" id="paginationLinks">
    {!! $logs->links('pagination::bootstrap-5') !!}
</div>

@endif
