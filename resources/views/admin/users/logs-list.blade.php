<style>
.hover-text {
    max-width: 200px !important;
    white-space: nowrap !important;
    overflow: hidden !important;
    text-overflow: ellipsis !important;
    position: relative;
}

.hover-trigger {
    cursor: pointer;
}

.live-tooltip {
    position: fixed;
    background: rgba(0,0,0,0.92);
    color: #fff;
    padding: 10px 14px;
    border-radius: 6px;
    max-width: 450px;
    font-size: 0.9em;
    z-index: 999999999;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    pointer-events: none;
    white-space: normal;
    line-height: 1.45;
    display: none;
}

.table-hover tbody tr:hover { background-color: #c7c7c71e; }
thead.table-light th { background-color: #4a90e2; color: #fff; text-align: center; }
.checkbox-col { width: 50px; text-align: center; }
tr.selected-row { background-color: #fff3cd !important; }
</style>

<div>
      {{-- Bulk delete --}}
    <div class="mb-2 d-flex justify-content-end align-items-center">
        <button
            class="btn btn-danger d-flex align-items-center"
            x-show="selectedIds.length > 0"
            x-cloak
            @click="bulkDelete()"
        >
            <i class="mdi mdi-delete me-1"></i>
            <span x-text="selectedIds.length"></span>
        </button>
    </div>


    <table class="table table-bordered table-hover align-middle">
        <thead class="table-light">
            <tr>
                <th>#</th>
                <th>Username</th>
                <th>Action</th>
                <th>IP Address</th>
                <th>User Agent</th>
                <th>Date</th>
                <th class="checkbox-col">
                  <label class="custom-checkbox d-flex justify-content-center align-items-center">
                      <input type="checkbox" x-model="allSelected" @click="toggleAll()" style="display:none;">
                      <i :class="allSelected ? 'mdi mdi-playlist-check' : 'mdi mdi-playlist-plus'"
                        style="font-size: 1.5rem; cursor:pointer; color:white ;"></i>
                  </label>
                </th>


            </tr>
        </thead>

        <tbody>
           @foreach ($logs as $log)
<tr :class="{ 'selected-row': selectedIds.includes({{ $log->id }}) }">
    <td>{{ $logs->firstItem() + $loop->index }}</td>
    <td>
        @if ($log->user)
            {{ $log->user->username }}
        @else
            <span class="text-danger fw-bold" title="Deleted account">{{ $log->username ?? 'Deleted User' }}</span>
        @endif
    </td>
    <td class="hover-text col-action" data-fulltext="{{ $log->action }}">{{ $log->action }}</td>
    <td>{{ $log->ip_address }}</td>
    <td class="hover-text" data-fulltext="{{ $log->user_agent }}">{{ $log->user_agent }}</td>
    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
    <td class="checkbox-col">
        <input type="checkbox" class="row-checkbox" value="{{ $log->id }}" @click="toggleSelect({{ $log->id }})">
    </td>
</tr>
@endforeach

        </tbody>
    </table>

    @if ($logs->isEmpty())
        <div class="alert alert-info text-center m-3">No user logs found.</div>
    @endif

    {{-- Pagination --}}
    @if ($logs->lastPage() > 1)
    <div class="d-flex justify-content-center mt-3">
        <nav>
            <ul class="pagination">
                {{-- Previous --}}
                <li class="page-item {{ $logs->currentPage() == 1 ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $logs->url($logs->currentPage()-1) }}">Previous</a>
                </li>

                {{-- Page numbers --}}
                @for ($i = 1; $i <= $logs->lastPage(); $i++)
                    <li class="page-item {{ $logs->currentPage() == $i ? 'active' : '' }}">
                        <a class="page-link" href="{{ $logs->url($i) }}">{{ $i }}</a>
                    </li>
                @endfor

                {{-- Next --}}
                <li class="page-item {{ $logs->currentPage() == $logs->lastPage() ? 'disabled' : '' }}">
                    <a class="page-link" href="{{ $logs->url($logs->currentPage()+1) }}">Next</a>
                </li>
            </ul>
        </nav>
    </div>
    @endif
</div>

<script>
  
</script>

<script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

