@php
$container = 'container-xxl';

// Venues from database (passed from controller/route)
// $venues is already fetched from database via route

$natures = [
    'Training/Seminar', 'Conference/Summit', 'Culmination', 'Socialization',
    'Meeting', 'Concert', 'Exhibit', 'Program', 'Educational Tour',
    'Clean and Green', 'Competition'
];

// Off-campus requirements checklist
$offCampusRequirements = [
    'curriculum_requirement' => 'Curriculum Requirement',
    'destination_handbook' => 'Destination Handbook or Manual',
    'notarized_parents_consent' => 'Notarized Parents Consent',
    'medical_certificate' => 'Medical Certificate',
    'personnel_in_charge' => 'Personnel-in-Charge',
    'first_aid_kit' => 'First Aid Kit',
    'fee_fund' => 'Fee/Fund',
    'insurance' => 'Insurance',
    'vehicle' => 'Vehicle',
    'lgu_ngo' => 'LGU/NGO',
    'orientation_briefing' => 'Orientation/Briefing',
    'learning_journals' => 'Learning Journals',
    'emergency_preparedness_plan' => 'Emergency Preparedness Plan',
];

// Get dates from URL parameters (from calendar selection)
$dateStart = request('date_start', '');
$dateEnd = request('date_end', '');
@endphp

@extends('layouts/contentNavbarLayout')
@section('title', 'Organization Activity Permit Form')

@section('vendor-style')
<link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" rel="stylesheet">
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
@endsection

@php
    $fullName =
        Auth::user()->profile?->first_name . ' ' .
        (Auth::user()->profile?->middle_name ? strtoupper(substr(Auth::user()->profile->middle_name, 0, 1)) . '. ' : '') .
        Auth::user()->profile?->last_name . ' ' .
        Auth::user()->profile?->suffix;
@endphp

@section('content')
<div class="{{ $container }} py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendar</a></li>
            <li class="breadcrumb-item active" aria-current="page">New Permit</li>
        </ol>
    </nav>

    <div class="card shadow-lg rounded-3">
        <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">SDSO Organization Activity Permit</h5>
            <a href="{{ route('calendar.index') }}" class="btn btn-sm btn-light">
                <i class="ti ti-arrow-left me-1"></i> Back to Calendar
            </a>
        </div>
        <div class="card-body">
            @if ($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            @if($dateStart)
            <div class="alert alert-info d-flex align-items-center">
                <i class="ti ti-calendar me-2"></i>
                <div>
                    <strong>Selected Date(s) from Calendar:</strong>
                    {{ \Carbon\Carbon::parse($dateStart)->format('F j, Y') }}
                    @if($dateEnd && $dateEnd !== $dateStart)
                        to {{ \Carbon\Carbon::parse($dateEnd)->format('F j, Y') }}
                    @endif
                </div>
            </div>
            @endif

            <form action="{{ route('permit.generate') }}" method="POST" enctype="multipart/form-data" id="permitForm">
                @csrf

                {{-- Basic Info --}}
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" value="{{ trim($fullName) }}" disabled>
                        <input type="hidden" name="name" value="{{ trim($fullName) }}">
                    </div>

                    <div class="col-md-6">
                        <label class="form-label">Organization <span class="text-danger">*</span></label>
                        <input type="text"
                               class="form-control"
                               value="{{ auth()->user()->organization->organization_name ?? 'No Organization Assigned' }}"
                               disabled>
                        <input type="hidden"
                               name="organization_id"
                               value="{{ auth()->user()->organization->organization_id ?? '' }}">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Title of Activity <span class="text-danger">*</span></label>
                    <input type="text" name="title_activity" class="form-control" value="{{ old('title_activity') }}" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Purpose <span class="text-danger">*</span></label>
                    <textarea name="purpose" class="form-control" rows="3" required>{{ old('purpose') }}</textarea>
                </div>

                <hr class="my-4">

                {{-- Type of Event & Nature --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Type of Event <span class="text-danger">*</span></label>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="In-Campus" id="type1"
                               {{ old('type') == 'In-Campus' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="type1">In-Campus</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="type" value="Off-Campus" id="type2"
                               {{ old('type') == 'Off-Campus' ? 'checked' : '' }}>
                        <label class="form-check-label" for="type2">Off-Campus</label>
                    </div>
                </div>

                {{-- Off-Campus Requirements Section --}}
                <div id="off_campus_requirements_section" style="display: none;">
                    <hr class="my-4">
                    <h6 class="fw-bold mb-3">Off-Campus Requirements</h6>
                    <p class="text-muted small">Select and upload the required documents for your off-campus activity:</p>

                    <div class="row">
                        @foreach ($offCampusRequirements as $key => $label)
                        <div class="col-md-6 mb-3">
                            <div class="card">
                                <div class="card-body p-3">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input requirement-checkbox" type="checkbox"
                                               name="requirements[]" value="{{ $key }}" id="req_{{ $key }}">
                                        <label class="form-check-label fw-semibold" for="req_{{ $key }}">
                                            {{ $label }}
                                        </label>
                                    </div>
                                    <input type="file" name="requirement_files[{{ $key }}]" id="file_{{ $key }}"
                                           class="form-control form-control-sm requirement-file"
                                           accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" disabled>
                                    <small class="text-muted">Accepted: PDF, DOC, DOCX, JPG, PNG</small>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Nature of Activity <span class="text-danger">*</span></label>
                    <div class="row">
                        @foreach ($natures as $nature)
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="nature" value="{{ $nature }}"
                                       id="nature_{{ $loop->index }}"
                                       {{ old('nature') == $nature ? 'checked' : '' }} required>
                                <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $nature }}</label>
                            </div>
                        </div>
                        @endforeach
                        <div class="col-md-6 mt-2">
                            <div class="input-group">
                                <div class="input-group-text">
                                    <input class="form-check-input mt-0" type="radio" name="nature" value="Other"
                                           id="nature_other_check" {{ old('nature') == 'Other' ? 'checked' : '' }}>
                                </div>
                                <input type="text" name="nature_other_text" id="nature_other_text"
                                       class="form-control" placeholder="Other (specify)"
                                       value="{{ old('nature_other_text') }}" disabled>
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Venue & Schedule --}}
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label class="form-label">Venue <span class="text-danger">*</span></label>
                        <select name="venue_id" id="venue_select" class="form-select" style="display: none;">
                            <option value="" disabled selected>Select In-Campus Venue</option>
                            @if(isset($venues) && $venues->count() > 0)
                                @foreach ($venues as $venue)
                                <option value="{{ $venue->venue_id }}" {{ old('venue_id') == $venue->venue_id ? 'selected' : '' }}>
                                    {{ $venue->venue_name }}
                                </option>
                                @endforeach
                            @else
                                <option value="" disabled>No venues available</option>
                            @endif
                        </select>
                        <input type="text" name="venue_other" id="venue_text" class="form-control"
                               placeholder="Enter Off-Campus Location" value="{{ old('venue_other') }}" style="display: none;">
                        <input type="hidden" name="venue" id="final_venue_name">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Start Date <span class="text-danger">*</span></label>
                        <input type="text" id="date_start" name="date_start" class="form-control"
                               placeholder="Select start date" value="{{ old('date_start', $dateStart) }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">End Date (optional)</label>
                        <input type="text" id="date_end" name="date_end" class="form-control"
                               placeholder="Select end date" value="{{ old('date_end', $dateEnd) }}">
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-md-6">
                        <label class="form-label">Start Time <span class="text-danger">*</span></label>
                        <input type="text" id="time_start" name="time_start" class="form-control"
                               placeholder="Select start time" value="{{ old('time_start') }}" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">End Time <span class="text-danger">*</span></label>
                        <input type="text" id="time_end" name="time_end" class="form-control"
                               placeholder="Select end time" value="{{ old('time_end') }}" required>
                    </div>
                </div>

                <hr class="my-4">

                {{-- Participants --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Participants <span class="text-danger">*</span></label><br>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="participants" value="Members"
                               id="members" {{ old('participants') == 'Members' ? 'checked' : '' }} required>
                        <label class="form-check-label" for="members">Members</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="participants" value="Officers"
                               id="officers" {{ old('participants') == 'Officers' ? 'checked' : '' }}>
                        <label class="form-check-label" for="officers">Officers</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="participants" value="All Students"
                               id="all_students" {{ old('participants') == 'All Students' ? 'checked' : '' }}>
                        <label class="form-check-label" for="all_students">All Students</label>
                    </div>
                    <div class="input-group mt-2" style="max-width: 400px;">
                        <div class="input-group-text">
                            <input class="form-check-input mt-0" type="radio" id="participants_other_check"
                                   name="participants" value="Other" {{ old('participants') == 'Other' ? 'checked' : '' }}>
                        </div>
                        <input type="text" class="form-control" name="participants_other_text"
                               id="participants_other_text" placeholder="Specify other participants"
                               value="{{ old('participants_other_text') }}" disabled>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label">Number of Participants <span class="text-danger">*</span></label>
                    <input type="number" name="number" class="form-control" min="1"
                           value="{{ old('number') }}" required>
                </div>

                <hr class="my-4">

                {{-- Signature (from database) --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Signature</label>
                    @if(auth()->user()->signature)
                    <div class="border rounded p-3 bg-light">
                        <img src="{{ asset('storage/' . Auth::user()->signature) }}" alt="Your Signature"
                             class="img-fluid" style="max-height: 150px;">
                    </div>
                    <small class="text-muted d-block mt-2">This is your registered signature. To update it, please visit your profile settings.</small>
                    @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> No signature on file. Please upload your signature in your profile settings before submitting this form.
                    </div>
                    @endif
                </div>

                <div class="d-flex justify-content-between">
                    <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary">
                        <i class="ti ti-arrow-left me-1"></i> Cancel
                    </a>
                    <button type="submit" class="btn btn-primary" {{ !auth()->user()->signature ? 'disabled' : '' }}>
                        <i class="ti ti-file-text me-1"></i> Generate PDF
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- Flatpickr Initialization ---
    const startDatePicker = flatpickr("#date_start", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        onChange: function(selectedDates, dateStr, instance) {
            const endDateInput = document.getElementById('date_end');
            if (endDateInput && endDateInput._flatpickr) {
                endDateInput._flatpickr.set('minDate', dateStr);
                if (endDateInput._flatpickr.selectedDates[0] && endDateInput._flatpickr.selectedDates[0] < selectedDates[0]) {
                    endDateInput._flatpickr.clear();
                }
            }
        }
    });

    flatpickr("#date_end", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
    });

    flatpickr("#time_start", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
    });

    flatpickr("#time_end", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
    });

    // --- Toggle Other Fields ---
    function toggleOtherField(radioName, textFieldId) {
        const radios = document.querySelectorAll(`input[name="${radioName}"]`);
        const textField = document.getElementById(textFieldId);
        if (!textField) return;

        radios.forEach(radio => {
            radio.addEventListener('change', function () {
                textField.disabled = this.value !== 'Other';
                if (this.value === 'Other') {
                    textField.setAttribute('required', 'required');
                } else {
                    textField.removeAttribute('required');
                    textField.value = '';
                }
            });
        });

        // Trigger on load if "Other" is already selected
        const checkedRadio = document.querySelector(`input[name="${radioName}"]:checked`);
        if (checkedRadio && checkedRadio.value === 'Other') {
            textField.disabled = false;
            textField.setAttribute('required', 'required');
        }
    }

    toggleOtherField('nature', 'nature_other_text');
    toggleOtherField('participants', 'participants_other_text');

    // --- Venue & Off-Campus Logic ---
    const venueSelect = document.getElementById('venue_select');
    const venueText = document.getElementById('venue_text');
    const finalVenueName = document.getElementById('final_venue_name');
    const typeRadios = document.querySelectorAll('input[name="type"]');
    const offCampusSection = document.getElementById('off_campus_requirements_section');

    function handleVenueToggle(typeValue) {
        if (typeValue === 'In-Campus') {
            venueSelect.style.display = 'block';
            venueText.style.display = 'none';
            offCampusSection.style.display = 'none';
            venueSelect.setAttribute('required', 'required');
            venueText.removeAttribute('required');
            venueText.value = '';

            document.querySelectorAll('.requirement-checkbox').forEach(cb => {
                cb.checked = false; cb.disabled = true;
            });
            document.querySelectorAll('.requirement-file').forEach(file => {
                file.disabled = true; file.value = '';
            });
        } else if (typeValue === 'Off-Campus') {
            venueSelect.style.display = 'none';
            venueText.style.display = 'block';
            offCampusSection.style.display = 'block';
            venueSelect.removeAttribute('required');
            venueText.setAttribute('required', 'required');
            venueSelect.value = '';

            document.querySelectorAll('.requirement-checkbox').forEach(cb => {
                cb.disabled = false;
            });
        }
        updateFinalVenueName();
    }

    function updateFinalVenueName() {
        if (venueSelect.style.display !== 'none' && venueSelect.value) {
            finalVenueName.value = venueSelect.options[venueSelect.selectedIndex].text;
        } else if (venueText.style.display !== 'none' && venueText.value) {
            finalVenueName.value = venueText.value;
        } else {
            finalVenueName.value = '';
        }
    }

    typeRadios.forEach(radio => radio.addEventListener('change', () => handleVenueToggle(radio.value)));
    venueSelect.addEventListener('change', updateFinalVenueName);
    venueText.addEventListener('input', updateFinalVenueName);

    // Initialize on load
    const checkedType = document.querySelector('input[name="type"]:checked');
    if (checkedType) {
        handleVenueToggle(checkedType.value);
    }

    // --- Requirement File Toggle ---
    document.querySelectorAll('.requirement-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            const fileInput = document.getElementById('file_' + this.value);
            if (this.checked) {
                fileInput.disabled = false;
                fileInput.setAttribute('required', 'required');
            } else {
                fileInput.disabled = true;
                fileInput.removeAttribute('required');
                fileInput.value = '';
            }
        });
    });

    // === Form Submission with AJAX & SweetAlert ===
    const form = document.getElementById('permitForm');
    const submitBtn = form.querySelector('button[type="submit"]');

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const hasSignature = {{ auth()->user()->signature ? 'true' : 'false' }};
        if (!hasSignature) {
            Swal.fire({
                icon: 'warning',
                title: 'Signature Missing',
                text: 'Please upload your signature in your profile settings.',
                confirmButtonColor: '#696cff'
            });
            return;
        }

        const isOffCampus = document.querySelector('input[name="type"]:checked')?.value === 'Off-Campus';
        if (isOffCampus) {
            const checkedBoxes = document.querySelectorAll('.requirement-checkbox:checked');
            let missingFile = false;
            checkedBoxes.forEach(cb => {
                const fileInput = document.getElementById('file_' + cb.value);
                if (!fileInput.files || fileInput.files.length === 0) {
                    missingFile = true;
                }
            });
            if (missingFile) {
                Swal.fire({
                    icon: 'error',
                    title: 'Missing Documents',
                    text: 'Please upload files for all selected off-campus requirements.',
                    confirmButtonColor: '#ff3e4d'
                });
                return;
            }
        }

        Swal.fire({
            title: 'Generating PDF...',
            text: 'Please wait while we prepare your permit.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            showConfirmButton: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.blob();
        })
        .then(blob => {
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: 'Your Activity Permit has been generated.',
                confirmButtonColor: '#71dd37'
            }).then(() => {
                // Redirect back to calendar
                window.location.href = '{{ route('calendar.index') }}';
            });
        })
        .catch(error => {
            console.error('Error:', error);

            let errorMessage = 'An error occurred while generating the PDF.';
            if (error.errors) {
                errorMessage = Object.values(error.errors).flat().join('<br>');
            } else if (error.message) {
                errorMessage = error.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Generation Failed',
                html: errorMessage,
                confirmButtonColor: '#ff3e4d'
            });
        });
    });
});
</script>
@endsection
