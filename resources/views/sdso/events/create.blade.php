{{-- resources/views/bargo/events/create.blade.php --}}
@php $container = 'container-xxl'; @endphp
@extends('layouts.contentNavbarLayout')

@section('title', 'BARGO • Create Event')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="{{ $container }} flex-grow-1 container-p-y">

    <!-- Page Header -->
    <div class="d-flex align-items-center justify-content-between mb-6">
        <div>
            <h3 class="fw-bold mb-2">Create BARGO Event</h3>
            <p class="text-muted mb-0">Official campus events — instantly approved and visible to all</p>
        </div>
        <a href="{{ route('bargo.events.calendar') }}" class="btn btn-outline-secondary">
            Back to Calendar
        </a>
    </div>

    <!-- Main Card -->
    <div class="card border-0 shadow-lg">
        <div class="card-body p-6">

            <form id="bargoEventForm" class="row g-5">

                @csrf
                <input type="hidden" name="type" value="In-Campus">

                <!-- Title -->
                <div class="col-12">
                    <label class="form-label fw-semibold text-dark">
                        Title of Activity <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="title_activity" class="form-control form-control-lg"
                           placeholder="e.g., BARGO General Assembly 2025" required>
                </div>

                <!-- Purpose -->
                <div class="col-12">
                    <label class="form-label fw-semibold text-dark">
                        Purpose of the Event <span class="text-danger">*</span>
                    </label>
                    <textarea name="purpose" class="form-control" rows="5"
                              placeholder="Describe the objective and significance..." required></textarea>
                </div>

                <!-- Nature -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        Nature of Activity <span class="text-danger">*</span>
                    </label>
                    <select name="nature" class="form-select form-select-lg" required>
                        <option value="">-- Select Nature --</option>
                        <option value="Training/Seminar">Training / Seminar</option>
                        <option value="Conference/Summit">Conference / Summit</option>
                        <option value="Meeting">Meeting</option>
                        <option value="Program">Program</option>
                        <option value="Competition">Competition</option>
                        <option value="Other">Other</option>
                    </select>
                </div>

                <div class="col-md-6" id="natureOther" style="display:none;">
                    <label class="form-label fw-semibold text-dark">
                        Specify Other Nature
                    </label>
                    <input type="text" name="nature_other_text" class="form-control" placeholder="e.g., Team Building">
                </div>

                <!-- Venue -->
                <div class="col-12">
                    <label class="form-label fw-semibold text-dark">
                        Venue <span class="text-danger">*</span>
                    </label>
                    <select name="venue" class="form-select form-select-lg" required>
                        <option value="">-- Select Venue --</option>
                        @foreach(\App\Models\Venue::orderBy('venue_name')->get() as $v)
                            <option value="{{ $v->venue_id }}">{{ $v->venue_name }}</option>
                        @endforeach
                    </select>
                </div>



                <!-- Date & Time -->
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        Start Date <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="date_start" class="form-control flatpickr-date"
                           value="{{ request('date') }}" placeholder="Select date" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        End Date
                    </label>
                    <input type="text" name="date_end" class="form-control flatpickr-date" placeholder="Optional">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        Start Time <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="time_start" class="form-control flatpickr-time"
                           placeholder="e.g., 09:00 AM" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold text-dark">
                        End Time <span class="text-danger">*</span>
                    </label>
                    <input type="text" name="time_end" class="form-control flatpickr-time"
                           placeholder="e.g., 05:00 PM" required>
                </div>

                <!-- Participants & Number -->
                <div class="col-lg-8">
                    <label class="form-label fw-semibold text-dark">
                        Participants
                    </label>
                    <input type="text" name="participants" class="form-control"
                           value="BARGO Members & Guests" placeholder="e.g., All Students">
                </div>
                <div class="col-lg-4">
                    <label class="form-label fw-semibold text-dark">
                        Expected Attendees
                    </label>
                    <input type="number" name="number" class="form-control" min="1" value="100">
                </div>

                <!-- Instant Approval Banner -->
                <div class="col-12">
                    <div class="alert alert-success border-0 shadow-sm p-4 mt-5">
                        <div class="d-flex align-items-start">
                            <div class="flex-shrink-0">
                                <i class="ti ti-shield-check text-success fs-1"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <h5 class="alert-heading mb-2">Instant Approval Guaranteed</h5>
                                <p class="mb-0">
                                    All BARGO events are <strong>automatically approved</strong> by all offices and will appear on the campus calendar immediately.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="col-12">
                    <div class="d-flex justify-content-end gap-3 mt-5 pt-4 border-top">
                        <a href="{{ route('bargo.events.calendar') }}" class="btn btn-outline-secondary btn-lg px-5">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary btn-lg px-6">
                            Create Event
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    flatpickr(".flatpickr-date", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        minDate: "today"
    });

    flatpickr(".flatpickr-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false,
        minuteIncrement: 15
    });

    const natureSelect = document.querySelector('[name="nature"]');
    const natureOther = document.getElementById('natureOther');
    natureSelect?.addEventListener('change', () => {
        natureOther.style.display = natureSelect.value === 'Other' ? 'block' : 'none';
    });

    document.getElementById('bargoEventForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const btn = this.querySelector('button[type="submit"]');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Creating...';

        const formData = new FormData(this);
        formData.append('type', 'In-Campus');

        fetch('{{ route('bargo.calendar.store') }}', {   // ← THIS IS THE CORRECT ONE
            method: 'POST',
            body: new URLSearchParams(formData),
            headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
        })
        .then(r => r.json())
        .then(d => {
            if (d.success) {
                Swal.fire('Success!', 'Event created and approved!', 'success')
                    .then(() => location.href = '{{ route('bargo.events.calendar') }}');
            } else {
                throw new Error(d.message || 'Failed');
            }
        })
        .catch(err => {
            console.error(err);
            Swal.fire('Error', err.message || 'Something went wrong', 'error');
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = 'Create Event';
        });
    });
});
</script>
@endsection
