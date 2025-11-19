@php
  $container = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')

@section('title', 'User Logs')

@section('content')
  <div class="{{ $container }} py-4">
    <div class="card shadow-sm">
      <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
        <h5 class="mb-0">User Activity Logs</h5>
      </div>

      <div class="card-body">
        @if ($logs->isEmpty())
          <div class="alert alert-info text-center">No user logs found.</div>
        @else
          <div class="table-responsive">
            <table id="logsTable" class="table table-bordered table-striped align-middle">
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
              <tbody id="logsBody">
                @foreach ($logs as $index => $log)
                  <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
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
          </div>

          <div class="d-flex justify-content-center mt-3" id="paginationWrapper"></div>
        @endif
      </div>
    </div>
  </div>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const searchInput = document.querySelector('#globalSearch'); // global search bar
      const tableBody = document.querySelector('#logsBody');
      const rows = Array.from(tableBody.querySelectorAll('tr'));
      const paginationWrapper = document.querySelector('#paginationWrapper');
      const rowsPerPage = 20;
      let currentPage = 1;

      // Render table function
      function renderTable(filteredRows) {
        const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
        const start = (currentPage - 1) * rowsPerPage;
        const end = start + rowsPerPage;
        const visibleRows = filteredRows.slice(start, end);

        tableBody.innerHTML = '';
        visibleRows.forEach(row => tableBody.appendChild(row));

        // Pagination buttons
        paginationWrapper.innerHTML = '';
        for (let i = 1; i <= totalPages; i++) {
          const btn = document.createElement('button');
          btn.textContent = i;
          btn.className = 'btn btn-sm ' + (i === currentPage ? 'btn-primary' : 'btn-outline-primary');
          btn.addEventListener('click', () => {
            currentPage = i;
            renderTable(filteredRows);
          });
          paginationWrapper.appendChild(btn);
        }
      }

      // Live search filter
      function applySearch() {
        const query = searchInput.value.toLowerCase().trim();
        const filtered = rows.filter(row => row.textContent.toLowerCase().includes(query));
        currentPage = 1; // always start from page 1 when searching
        renderTable(filtered);
      }

      if (searchInput) {
        searchInput.addEventListener('input', applySearch);
      }

      // Initial table
      renderTable(rows);
    });
  </script>
@endsection