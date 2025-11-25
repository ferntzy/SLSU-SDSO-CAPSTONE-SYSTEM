@php
  $container = 'container-xxl';
  $containerNav = 'container-xxl';
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'User Activity Logs')

@section('content')

<div class="card">
  <div class="card-header d-flex justify-content-between align-items-center">

    <div class="header-title">
      <h5 class="mb-0 text-dark">USER ACTIVITY LOGS</h5>
    </div>

    <div class="card-action">
      <div class="input-group">
        <input type="text" id="searchAccountlogs" class="form-control" placeholder="Search Account Logs">
        <button class="input-group-text" id="btnSearchlogs">
          <i class="mdi mdi-account-search-outline"></i>
        </button>
      </div>
    </div>

  </div>

  <div class="table-responsive text-nowrap" id="logsList">
      @include('admin.users.logs-list')
  </div>

</div>

@endsection

@section('page-script')
<script>
  // Pagination + Search Handling
  document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.querySelector('#searchAccountlogs');
    const tableBody = document.querySelector('#logsBody');
    const rows = Array.from(tableBody.querySelectorAll('tr'));
    const paginationWrapper = document.querySelector('#paginationWrapper');
    const rowsPerPage = 20;
    let currentPage = 1;

    function renderTable(filteredRows) {
      const totalPages = Math.ceil(filteredRows.length / rowsPerPage);
      const start = (currentPage - 1) * rowsPerPage;
      const end = start + rowsPerPage;
      const visibleRows = filteredRows.slice(start, end);

      tableBody.innerHTML = '';
      visibleRows.forEach(row => tableBody.appendChild(row));

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

    // Initial load
    renderTable(rows);
  });
</script>
@endsection
@section('page-script')
@include('admin.users.js')

