{{-- resources/views/student/permit/form.blade.php --}}
@php
    use Carbon\Carbon;
$user = Auth::user();
    $profileId = \DB::table('users')->where('user_id', $user->user_id)->value('profile_id');
    $member = \DB::table('members')->where('profile_id', $profileId)->first();
    $organization = $member ? \App\Models\Organization::find($member->organization_id) : null;
    $fullName = trim(
        Auth::user()->profile?->first_name . ' ' .
        (Auth::user()->profile?->middle_name ? strtoupper(substr(Auth::user()->profile->middle_name, 0, 1)) . '. ' : '') .
        Auth::user()->profile?->last_name . ' ' .
        (Auth::user()->profile?->suffix ?? '')
    );

    $natures = [
        'Training/Seminar', 'Conference/Summit', 'Culmination', 'Socialization',
        'Meeting', 'Concert', 'Exhibit', 'Program', 'Educational Tour',
        'Clean and Green', 'Competition'
    ];

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

    $displayStart = $dateStart ? Carbon::parse($dateStart)->format('F j, Y') : null;
    $displayEnd   = $dateEnd && $dateEnd !== $dateStart ? Carbon::parse($dateEnd)->format('F j, Y') : null;
@endphp

@extends('layouts.contentNavbarLayout')

@section('title', 'New Activity Permit')

@section('vendor-style')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
@endsection

@section('vendor-script')
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@endsection

@section('content')
<div class="container-xxl flex-grow-1 container-p-y">

    <!-- Breadcrumb + Back Button -->
    <div class="d-flex align-items-center justify-content-between mb-5">
        <nav aria-label="breadcrumb" class="d-inline-block">
            <ol class="breadcrumb breadcrumb-style1 mb-0">
                <li class="breadcrumb-item"><a href="{{ route('calendar.index') }}">Calendar</a></li>
                <li class="breadcrumb-item active text-primary">New Activity Permit</li>
            </ol>
        </nav>
        <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary btn-sm">
            Back to Calendar
        </a>
    </div>

    @if(!$organization)
                <div class="alert alert-danger">
                    <strong>Warning:</strong> You are not a member of any student organization.
                    Please contact admin to be added.
                </div>
                @php return; @endphp
            @endif

    <div class="row justify-content-center">
        <div class="col-xl-10 col-lg-11">

            <!-- Selected Date Info -->
            @if($dateStart)
                <div class="d-flex align-items-center text-muted mb-4">
                    <i class="ti ti-calendar-event me-2 text-primary"></i>
                    <span>
                        <strong>{{ $displayStart }}</strong>
                        @if($displayEnd) → {{ $displayEnd }}@endif
                        @if(!$displayEnd) <small class="text-muted">(Single-day event)</small>@endif
                    </span>
                </div>
            @endif

            <!-- Main Form Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-body p-5 p-xl-6">

                    <div class="text-center mb-6">
                        <h3 class="fw-bold text-dark mb-2">Activity Permit Application</h3>
                        <p class="text-muted">Fill out the details below to generate your official permit</p>
                    </div>

                    @if($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show mb-5">
                            <i class="ti ti-alert-circle me-2"></i>
                            <strong>Please correct the following:</strong>
                            <ul class="mt-2 mb-0 ps-4">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form id="permitForm" action="{{ route('permit.generate') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <!-- Applicant Info -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Applicant Name</label>
                                <input type="text" class="form-control form-control-lg" value="{{ $fullName }}" disabled>
                                <input type="hidden" name="name" value="{{ $fullName }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Organization</label>
                                <input type="text" class="form-control form-control-lg"
                                       value="{{ $organization->organization_name ?? 'Not Assigned' }}" disabled>
                                <input type="hidden" name="organization_id" value="{{ $organization->organization_id }}">
                            </div>
                        </div>

                        <!-- Activity Title & Purpose -->
                        <div class="mb-5">
                            <label class="form-label fw-medium">Activity Title <span class="text-danger">*</span></label>
                            <input type="text" name="title_activity" class="form-control form-control-lg"
                                   placeholder="e.g., Leadership Summit 2025" value="{{ old('title_activity') }}" required>
                        </div>

                        <div class="mb-5">
                            <label class="form-label fw-medium">Purpose of Activity <span class="text-danger">*</span></label>
                            <textarea name="purpose" class="form-control" rows="4"
                                      placeholder="Briefly describe the objectives and importance..."
                                      required>{{ old('purpose') }}</textarea>
                        </div>

                        <!-- Event Type & Venue -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-6">
                                <label class="form-label fw-medium">Type of Event <span class="text-danger">*</span></label>
                                <div class="d-flex gap-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" value="In-Campus" id="type_in" checked required>
                                        <label class="form-check-label fw-medium" for="type_in">In-Campus</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="type" value="Off-Campus" id="type_off">
                                        <label class="form-check-label fw-medium" for="type_off">Off-Campus</label>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label fw-medium">Venue <span class="text-danger">*</span></label>
                                <select name="venue_id" id="venue_select" class="form-select form-select-lg" required>
                                    <option value="" disabled selected>Select venue...</option>
                                    @foreach($venues as $venue)
                                        <option value="{{ $venue->venue_id }}" {{ old('venue_id') == $venue->venue_id ? 'selected' : '' }}>
                                            {{ $venue->venue_name }}
                                        </option>
                                    @endforeach
                                </select>

                                <input type="text" name="venue_other" id="venue_text" class="form-control mt-3"
                                       placeholder="e.g., Baguio City, Subic Bay" value="{{ old('venue_other') }}" style="display:none;">
                                <input type="hidden" name="venue" id="final_venue_name">
                            </div>
                        </div>

                        <!-- Off-Campus Requirements -->
                        <div id="off_campus_requirements_section" class="border-start border-warning border-4 ps-4 mb-5" style="display:none;">
                            <h6 class="text-warning fw-bold mb-3">Required Documents for Off-Campus Activities</h6>
                            <div class="row g-3">
                                @foreach($offCampusRequirements as $key => $label)
                                    <div class="col-lg-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input requirement-checkbox" type="checkbox"
                                                   name="requirements[]" value="{{ $key }}" id="req_{{ $key }}">
                                            <label class="form-check-label" for="req_{{ $key }}">{{ $label }}</label>
                                        </div>
                                        <input type="file" name="requirement_files[{{ $key }}]" id="file_{{ $key }}"
                                               class="form-control form-control-sm requirement-file" disabled
                                               accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Nature of Activity -->
                        <div class="mb-5">
                            <label class="form-label fw-medium">Nature of Activity <span class="text-danger">*</span></label>
                            <div class="row g-3">
                                @foreach($natures as $nature)
                                    <div class="col-md-4 col-sm-6">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="nature" value="{{ $nature }}"
                                                   id="nature_{{ $loop->index }}" {{ old('nature') == $nature ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $nature }}</label>
                                        </div>
                                    </div>
                                @endforeach
                                <div class="col-12 mt-3">
                                    <div class="input-group">
                                        <span class="input-group-text">
                                            <input class="form-check-input mt-0" type="radio" name="nature" value="Other"
                                                   id="nature_other_check" {{ old('nature') == 'Other' ? 'checked' : '' }}>
                                        </span>
                                        <input type="text" name="nature_other_text" id="nature_other_text" class="form-control"
                                               placeholder="Specify other nature..." value="{{ old('nature_other_text') }}" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Schedule -->
                        <div class="row g-4 mb-5">
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Start Date <span class="text-danger">*</span></label>
                                <input type="text" id="date_start" name="date_start" class="form-control flatpickr-date"
                                       value="{{ old('date_start', $dateStart) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">End Date</label>
                                <input type="text" id="date_end" name="date_end" class="form-control flatpickr-date"
                                       value="{{ old('date_end', $dateEnd) }}">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">Start Time <span class="text-danger">*</span></label>
                                <input type="text" id="time_start" name="time_start" class="form-control flatpickr-time" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-medium">End Time <span class="text-danger">*</span></label>
                                <input type="text" id="time_end" name="time_end" class="form-control flatpickr-time" required>
                            </div>
                        </div>

                        <!-- Participants -->
                        <div class="row g-4 mb-5">
                            <div class="col-lg-8">
                                <label class="form-label fw-medium">Participants <span class="text-danger">*</span></label>
                                <div class="d-flex flex-wrap gap-4">
                                    @foreach(['Members', 'Officers', 'All Students'] as $opt)
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="participants" value="{{ $opt }}"
                                                   id="part_{{ strtolower(str_replace(' ', '_', $opt)) }}"
                                                   {{ old('participants') == $opt ? 'checked' : '' }} required>
                                            <label class="form-check-label" for="part_{{ strtolower(str_replace(' ', '_', $opt)) }}">{{ $opt }}</label>
                                        </div>
                                    @endforeach
                                </div>
                                <div class="input-group mt-3" style="max-width: 480px;">
                                    <span class="input-group-text">
                                        <input class="form-check-input mt-0" type="radio" name="participants" value="Other"
                                               id="participants_other_check" {{ old('participants') == 'Other' ? 'checked' : '' }}>
                                    </span>
                                    <input type="text" name="participants_other_text" id="participants_other_text"
                                           class="form-control" placeholder="e.g., Selected Grade 11 STEM Students" disabled>
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <label class="form-label fw-medium">Estimated Number <span class="text-danger">*</span></label>
                                <input type="number" name="number" class="form-control form-control-lg" min="1"
                                       value="{{ old('number') }}" placeholder="50" required>
                            </div>
                        </div>

                        <!-- Signature Preview -->
                        <div class="text-center py-5 border-top">
                            <div class="mb-4">
                                <h6 class="fw-bold text-dark">Your Signature</h6>
                                @if(Auth::user()->signature)
                                    <div class="p-4 bg-light rounded-3 d-inline-block shadow-sm">
                                        <img src="{{ asset('storage/' . Auth::user()->signature) }}"
                                             alt="Signature" class="img-fluid" style="max-height: 120px;">
                                    </div>
                                    <p class="text-success mt-3">Ready for permit generation</p>
                                @else
                                    <div class="alert alert-warning d-inline-block px-5">
                                        Please <a href="{{ route('profile.update') }}" class="alert-link">upload your signature</a> first.
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex justify-content-between align-items-center pt-4 border-top">
                            <a href="{{ route('calendar.index') }}" class="btn btn-outline-secondary btn-lg">
                                Cancel
                            </a>
                            <button type="submit" class="btn btn-primary btn-lg px-5" id="submitBtn"
                                    {{ !Auth::user()->signature ? 'disabled' : '' }}>
                                Generate PDF Permit
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // Flatpickr
    flatpickr("#date_start", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        defaultDate: "{{ $dateStart ?? '' }}",
        minDate: "today",
        onChange: (d, s) => document.getElementById('date_end')._flatpickr.set('minDate', s)
    });

    flatpickr("#date_end", {
        dateFormat: "Y-m-d",
        altInput: true,
        altFormat: "F j, Y",
        defaultDate: "{{ $dateEnd ?? '' }}",
        minDate: "{{ $dateStart ?? 'today' }}"
    });

    flatpickr(".flatpickr-time", {
        enableTime: true,
        noCalendar: true,
        dateFormat: "h:i K",
        time_24hr: false
    });

    // Toggle Logic
    const toggle = () => {
        const off = document.querySelector('input[name="type"]:checked')?.value === 'Off-Campus';
        $('#venue_select').toggle(!off);
        $('#venue_text').toggle(off);
        $('#off_campus_requirements_section').toggle(off);
        $('#venue_select')[0].required = !off;
        $('#venue_text')[0].required = off;
        document.getElementById('final_venue_name').value = off
            ? document.getElementById('venue_text').value.trim()
            : document.getElementById('venue_select').selectedOptions[0]?.textContent.trim() || '';
    };

    document.querySelectorAll('input[name="type"]').forEach(el => el.addEventListener('change', toggle));
    document.getElementById('venue_select').addEventListener('change', toggle);
    document.getElementById('venue_text').addEventListener('input', toggle);

    // Other field toggles
    ['nature', 'participants'].forEach(field => {
        document.querySelectorAll(`input[name="${field}"]`).forEach(radio => {
            radio.addEventListener('change', () => {
                const input = document.getElementById(`${field}_other_text`);
                if (input) {
                    input.disabled = radio.value !== 'Other';
                    input.required = radio.value === 'Other';
                }
            });
        });
    });

    document.querySelectorAll('.requirement-checkbox').forEach(cb => {
        cb.addEventListener('change', function() {
            document.getElementById('file_' + this.value).disabled = !this.checked;
        });
    });

    toggle();

    // ========== AJAX SUBMISSION WITH SWEETALERT2 ==========
    const form = document.getElementById('permitForm');
    const submitBtn = document.getElementById('submitBtn');

    form.addEventListener('submit', function(e) {
        e.preventDefault();

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span> Generating Permit...';

        const formData = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
            }
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw err; });
            }
            return response.blob();
        })
        .then(blob => {
            const pdfUrl = window.URL.createObjectURL(blob);

            Swal.fire({
                icon: 'success',
                title: 'Success!',
                html: '<p>Your <strong>Activity Permit</strong> has been generated and submitted for approval.</p>',
                showCancelButton: true,

                reverseButtons: true,
                buttonsStyling: false,
                customClass: {
                    confirmButton: 'btn btn-primary me-3 px-4',
                    cancelButton: 'btn btn-success px-4'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open(pdfUrl, '_blank');
                } else if (result.dismiss === Swal.DismissReason.cancel) {
                    const a = document.createElement('a');

                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                }

                // Redirect to tracking page after a moment

            });
        })
        .catch(error => {
            console.error('Error:', error);
            let msg = 'Something went wrong. Please try again.';
            if (error.errors) {
                msg = Object.values(error.errors).flat().join('<br>');
            } else if (error.message) {
                msg = error.message;
            }

            Swal.fire({
                icon: 'error',
                title: 'Failed to Generate Permit',
                html: msg,
                confirmButtonText: 'Close',
                customClass: { confirmButton: 'btn btn-danger' }
            });
        })
        .finally(() => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = 'Generate PDF Permit';
        });
    });
});
</script>
@endsection
