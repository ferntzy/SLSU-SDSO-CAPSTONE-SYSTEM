@php
    $container = 'container-xxl';
    // --- DATABASE DATA STRUCTURES ---
    // The variables $organizations and $venues are now assumed to be passed from the controller,
    // containing collections of Organization and Venue objects, respectively.

    // --- MOCK/ASSUMED DATA STRUCTURES (Retained only for local canvas execution) ---
    $organizations = $organizations ?? collect([
        (object)['organization_id' => 1, 'organization_name' => 'Student Council'],
        (object)['organization_id' => 2, 'organization_name' => 'Computer Society'],
        (object)['organization_id' => 3, 'organization_name' => 'Literary Club'],
    ]);
    $venues = $venues ?? collect([
        (object)['venue_id' => 101, 'venue_name' => 'Auditorium A'],
        (object)['venue_id' => 102, 'venue_name' => 'Covered Court'],
        (object)['venue_id' => 103, 'venue_name' => 'Function Hall B'],
    ]);
    // ----------------------------------------------------------------------------

    $natures = [
        'Training/Seminar', 'Conference/Summit', 'Culmination', 'Socialization',
        'Meeting', 'Concert', 'Exhibit', 'Program', 'Educational Tour',
        'Clean and Green', 'Competition'
    ];
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
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
@endsection

@section('content')
    <div class="{{ $container }} py-4">
        <div class="card shadow-lg rounded-3">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">SDSO Organization Activity Permit</h5>
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

                <form action="{{ route('permit.generate') }}" method="POST" enctype="multipart/form-data" id="permitForm">
                    @csrf

                    {{-- Basic Info --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Name <span class="text-danger">*</span></label>
                            {{-- Assuming auth()->user()->name is available and user is authenticated --}}
                            <input type="text" name="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
                        </div>
                        <div class="col-md-6">

                          <label class="form-label">Organization <span class="text-danger">*</span></label>
                            <label name="organization_id" class="form-select" required>
                                @foreach ($organizations as $org)
                                    <option value="{{ $org->organization_id }}">{{ $org->organization_name }}</option>
                                @endforeach
                            </label>



                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Title of Activity <span class="text-danger">*</span></label>
                        <input type="text" name="title_activity" class="form-control" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Purpose <span class="text-danger">*</span></label>
                        <textarea name="purpose" class="form-control" rows="3" required></textarea>
                    </div>

                    <hr class="my-4">

                    {{-- Type of Event & Nature --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Type of Event <span class="text-danger">*</span></label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" value="In-Campus" id="type1" required>
                            <label class="form-check-label" for="type1">In-Campus</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="type" value="Off-Campus" id="type2">
                            <label class="form-check-label" for="type2">Off-Campus</label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Nature of Activity <span class="text-danger">*</span></label>
                        <div class="row">
                            @foreach ($natures as $nature)
                                <div class="col-md-4">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="nature" value="{{ $nature }}"
                                            id="nature_{{ $loop->index }}" required>
                                        <label class="form-check-label" for="nature_{{ $loop->index }}">{{ $nature }}</label>
                                    </div>
                                </div>
                            @endforeach

                            <div class="col-md-6 mt-2">
                                <div class="input-group">
                                    <div class="input-group-text">
                                        <input class="form-check-input mt-0" type="radio" name="nature" value="Other" id="nature_other_check">
                                    </div>
                                    <input type="text" name="nature_other_text" id="nature_other_text" class="form-control" placeholder="Other (specify)" disabled>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Venue & Schedule --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Venue <span class="text-danger">*</span></label>
                            {{-- In-Campus Venues (Dropdown) --}}
                            <select name="venue_id" id="venue_select" class="form-select" style="display: none;">
                                <option value="" disabled selected>Select In-Campus Venue</option>
                                {{-- Dynamically populated from $venues passed by the controller --}}
                                @foreach ($venues as $venue)
                                    <option value="{{ $venue->venue_id }}">{{ $venue->venue_name }}</option>
                                @endforeach
                            </select>
                            {{-- Off-Campus Venue (Text Input) --}}
                            <input type="text" name="venue_other" id="venue_text" class="form-control" placeholder="Enter Off-Campus Location" style="display: none;">

                            {{-- Hidden input to capture the final venue name for submission --}}
                            <input type="hidden" name="venue" id="final_venue_name">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Start Date <span class="text-danger">*</span></label>
                            {{-- Date fix: Removed minDate="today" in JS for flexibility --}}
                            <input type="text" id="date_start" name="date_start" class="form-control" placeholder="Select start date" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">End Date (optional)</label>
                            {{-- Date fix: Removed minDate="today" in JS for flexibility --}}
                            <input type="text" id="date_end" name="date_end" class="form-control" placeholder="Select end date">
                        </div>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Start Time <span class="text-danger">*</span></label>
                            <input type="text" id="time_start" name="time_start" class="form-control" placeholder="Select start time" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">End Time <span class="text-danger">*</span></label>
                            <input type="text" id="time_end" name="time_end" class="form-control" placeholder="Select end time" required>
                        </div>
                    </div>

                    {{-- Participants --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Participants <span class="text-danger">*</span></label><br>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="participants" value="Members" id="members" required>
                            <label class="form-check-label" for="members">Members</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="participants" value="Officers" id="officers">
                            <label class="form-check-label" for="officers">Officers</label>
                        </div>

                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="participants" value="All Students" id="all_students">
                            <label class="form-check-label" for="all_students">All Students</label>
                        </div>

                        <div class="input-group mt-2" style="max-width: 400px;">
                            <div class="input-group-text">
                                <input class="form-check-input mt-0" type="radio" id="participants_other_check" name="participants" value="Other">
                            </div>
                            <input type="text" class="form-control" name="participants_other_text" id="participants_other_text"
                                placeholder="Specify other participants" disabled>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Number of Participants <span class="text-danger">*</span></label>
                        <input type="number" name="number" class="form-control" min="1" required>
                    </div>

                    <hr class="my-4">

                    {{-- Signature --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold">Signature <span class="text-danger">*</span></label>
                        <small class="text-muted d-block mb-2">You can either upload a signature image or draw one below. (Required for submission)</small>
                        <input type="file" name="signature_upload" id="signature_upload" accept="image/*" class="form-control mb-2">

                        <canvas id="signature-pad"
                            style="border: 1px solid #ccc; width: 100%; height: 200px; touch-action: none;"></canvas>
                        <div class="mt-2">
                            <button type="button" class="btn btn-sm btn-secondary" id="clear-signature">Clear Signature</button>
                        </div>
                        <input type="hidden" name="signature_data" id="signature_data">
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary">Generate PDF</button>
                    </div>

                </form>
            </div>
        </div>
    </div>

    {{-- JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {

            // --- 1. Flatpickr Initialization (Date Fix Applied) ---

            // Start Date: No initial minimum constraint for maximum flexibility in date selection.
            const startDatePicker = flatpickr("#date_start", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                // minDate: "today" removed for flexibility
                onChange: function(selectedDates, dateStr, instance) {
                    // Automatically update minDate for the end date picker
                    const endDateInput = document.getElementById('date_end');
                    if (endDateInput && endDateInput._flatpickr) {
                        // Ensure end date cannot be before the selected start date
                        endDateInput._flatpickr.set('minDate', dateStr);

                        // Clear end date if it falls before the new start date
                        if (endDateInput._flatpickr.selectedDates[0] && endDateInput._flatpickr.selectedDates[0] < selectedDates[0]) {
                            endDateInput._flatpickr.clear();
                        }
                    }
                }
            });

            // End Date: No initial minimum constraint, relies on start date's onChange handler to set minDate.
            flatpickr("#date_end", {
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "F j, Y",
                // minDate: "today" removed for flexibility
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

            // --- 2. Dynamic "Other" Field Toggle ---
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
                        }
                        if (this.value !== 'Other') textField.value = '';
                    });
                });
            }
            toggleOtherField('nature', 'nature_other_text');
            toggleOtherField('participants', 'participants_other_text');

            // --- 3. Venue Toggle Logic ---
            const venueSelect = document.getElementById('venue_select');
            const venueText = document.getElementById('venue_text');
            const finalVenueName = document.getElementById('final_venue_name');
            const typeRadios = document.querySelectorAll('input[name="type"]');

            function handleVenueToggle(typeValue) {
                if (typeValue === 'In-Campus') {
                    venueSelect.style.display = 'block';
                    venueText.style.display = 'none';
                    venueSelect.setAttribute('required', 'required');
                    venueText.removeAttribute('required');
                    venueText.value = ''; // Clear off-campus text
                } else if (typeValue === 'Off-Campus') {
                    venueSelect.style.display = 'none';
                    venueText.style.display = 'block';
                    venueSelect.removeAttribute('required');
                    venueText.setAttribute('required', 'required');
                    venueSelect.value = ''; // Clear in-campus selection
                } else {
                    // Default state if nothing is selected
                    venueSelect.style.display = 'none';
                    venueText.style.display = 'none';
                    venueSelect.removeAttribute('required');
                    venueText.removeAttribute('required');
                }
                updateFinalVenueName();
            }

            // Function to update the hidden 'venue' field with the selected/typed value
            function updateFinalVenueName() {
                if (venueSelect.style.display !== 'none' && venueSelect.value) {
                    // Use the selected option's text as the venue name
                    finalVenueName.value = venueSelect.options[venueSelect.selectedIndex].text;
                } else if (venueText.style.display !== 'none' && venueText.value) {
                    finalVenueName.value = venueText.value;
                } else {
                    finalVenueName.value = '';
                }
            }

            typeRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    handleVenueToggle(this.value);
                });
            });

            venueSelect.addEventListener('change', updateFinalVenueName);
            venueText.addEventListener('input', updateFinalVenueName);
            // Initial call to hide fields until type is selected
            handleVenueToggle(document.querySelector('input[name="type"]:checked')?.value);


            // --- 4. Signature Pad Logic ---
            const canvas = document.getElementById('signature-pad');
            const signaturePad = new SignaturePad(canvas, {
                backgroundColor: 'rgba(255, 255, 255, 0)',
                penColor: 'black',
                minWidth: 1,
                maxWidth: 3,
            });

            // Resize canvas for high-DPI displays and responsiveness
            function resizeCanvas() {
                const ratio = Math.max(window.devicePixelRatio || 1, 1);
                canvas.width = canvas.offsetWidth * ratio;
                canvas.height = canvas.offsetHeight * ratio;
                canvas.getContext('2d').scale(ratio, ratio);
                // Preserve signature on resize, if any
                if (!signaturePad.isEmpty()) {
                    const data = signaturePad.toData();
                    signaturePad.clear();
                    signaturePad.fromData(data);
                }
            }
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            document.getElementById('clear-signature').addEventListener('click', function () {
                signaturePad.clear();
            });


            // --- 5. Form Submission and Final Signature Data Prep/Validation ---
            document.getElementById('permitForm').addEventListener('submit', function (e) {
                const signatureUpload = document.getElementById('signature_upload');

                // 1. Check if signature is present (either drawn or uploaded)
                const isPadEmpty = signaturePad.isEmpty();
                const hasUploadedFile = signatureUpload.files.length > 0;

                if (isPadEmpty && !hasUploadedFile) {
                    e.preventDefault(); // Stop submission
                    Swal.fire({
                        icon: 'warning',
                        title: 'Signature Required',
                        text: 'Please draw your signature on the pad or upload a signature image.',
                    });
                    return;
                }

                // 2. If drawn, convert to data URL and save to hidden field
                if (!isPadEmpty) {
                    const dataURL = signaturePad.toDataURL('image/png');
                    document.getElementById('signature_data').value = dataURL;
                    // The drawn signature will be used; the file upload field should be ignored if pad is used.
                }
                // If the pad is empty, the file upload input is used by the browser/server.
            });

        });
    </script>
@endsection
