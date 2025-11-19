@extends('layouts/contentNavbarLayout')
@section('title', 'Review Event Approval')

@section('content')
  <div class="container py-4">
    <div class="card shadow">
      <div class="card-header bg-primary text-white">
        <h5>Review Event: {{ $event->event_title }}</h5>
      </div>
      <div class="card-body">
        <form id="approvalForm">
          @csrf
          <div class="mb-3">
            <label class="form-label">Comments</label>
            <textarea name="comments" class="form-control" rows="3"></textarea>
          </div>
          <div class="text-end">
            <button type="button" class="btn btn-success" id="approveBtn">Approve</button>
            <button type="button" class="btn btn-danger" id="rejectBtn">Reject</button>
          </div>
        </form>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    document.getElementById('approveBtn').addEventListener('click', () => submitDecision('approved'));
    document.getElementById('rejectBtn').addEventListener('click', () => submitDecision('rejected'));

    function submitDecision(status) {
      Swal.fire({
        title: `Are you sure you want to ${status} this request?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Yes, proceed',
      }).then(result => {
        if (result.isConfirmed) {
          fetch('{{ route("approval.review", Crypt::encryptString($event->event_id)) }}', {
            method: 'POST',
            headers: {
              'X-CSRF-TOKEN': '{{ csrf_token() }}',
              'Content-Type': 'application/json',
            },
            body: JSON.stringify({
              status: status,
              comments: document.querySelector('[name="comments"]').value,
            }),
          })
            .then(res => res.json())
            .then(data => {
              Swal.fire('Success!', `Event ${status} successfully.`, 'success')
                .then(() => window.location.href = "{{ url()->previous() }}");
            });
        }
      });
    }
  </script>
@endsection