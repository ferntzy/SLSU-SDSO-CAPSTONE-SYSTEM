

<div class="modal fade" id="addOfficersModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-m modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
              <h5 class="modal-title w-100 text-center" id="officerModalTitle">
                  Add Officer
              </h5>


                <button type="button" class="btn-close position-absolute end-0 me-2" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-0">
              <div class="overflow-auto p-3" style="max-height: 70vh;">
                <div class="row g-3">

                    @foreach($roles as $position)
                      <div class="col-md-12">
                          <label class="form-label">{{ $position->RoleName }}</label>

                          <div class="input-group">
                              <span class="input-group-text">
                                  <i class="mdi mdi-account-circle"></i>
                              </span>

                            <select class="form-select officer-select" name="{{ Str::slug($position->RoleName, '_') }}" data-role="{{ $position->RoleName }}">
                              <option value="">Select</option>
                              @foreach($students as $student)
                                  <option value="{{ $student->profile_id }}">
                                      {{ $student->last_name }}, {{ $student->first_name }}
                                  </option>
                              @endforeach
                          </select>

                          </div>
                      </div>
                    @endforeach


                </div>

                <!-- Divider Line -->
                <div class="text-center my-2">
                    <hr style="width: 100%; border-top: 1px solid #424242;">
                </div>


                   <div class="text-center my-3">
                      <strong>- Other Position -</strong>
                  </div>

                <!-- ====================== TABS CARD ====================== -->
                <div class="col-md-12 mt-3">
                    <div class="card">

                        <!-- Tabs Header -->
                        <div class="card-header p-0">
                            <ul class="nav nav-tabs" role="tablist">

                                <li class="nav-item">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-representatives">Representatives</button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-industrial">Industrial</button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-engineering">Engineering</button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ssc">SSC</button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-ambassador">Ambassador</button>
                                </li>

                                <li class="nav-item">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-rotary">Rotary</button>
                                </li>

                            </ul>
                        </div>

                        <!-- ====================== TAB CONTENT ====================== -->
                        <div class="card-body">
                            <div class="tab-content p-0">

                               <!-- REPRESENTATIVES TAB -->
                            <div class="tab-pane fade show active" id="tab-representatives">
                                <div class="row g-2">
                                    @for($i = 1; $i <= 4; $i++)
                                        <div class="col-md-6 mt-2">
                                            <label class="form-label">
                                                {{ $i }}{{ $i == 1 ? 'st' : ($i == 2 ? 'nd' : ($i == 3 ? 'rd' : 'th')) }} Year Representative
                                            </label>

                                            <div class="input-group">
                                                <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                <select class="form-select" id="rep{{ $i }}Select" name="rep{{ $i }}">
                                                    <option value="">Select</option>
                                                    @foreach($officers as $officer)
                                                        <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    @endfor

                                    <!-- Creative Officer -->
                                    <div class="col-md-6 mt-2">
                                        <label class="form-label">Creative Officer</label>
                                        <div class="input-group">
                                            <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                            <select class="form-select" id="creativeOfficerSelect" name="creative_officer">
                                                <option value="">Select</option>
                                                @foreach($officers as $officer)
                                                    <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                </div>
                            </div>


                                {{-- <!-- INDUSTRIAL TAB -->
                                <div class="tab-pane fade" id="tab-industrial">
                                    <div class="row g-3">

                                        @php
                                            $industrial_positions = [
                                                'Drafting', 'Culinary', 'Electrical',
                                                'Electronics', 'HVACR', 'Automotive'
                                            ];
                                        @endphp

                                        @foreach($industrial_positions as $position)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $position }}</label>

                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" id="{{ Str::slug($position, '') }}Select">
                                                        <option value="">Select</option>
                                                        @foreach($officers as $officer)
                                                            <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div> --}}

                                {{-- <!-- ENGINEERING TAB -->
                                <div class="tab-pane fade" id="tab-engineering">
                                    <div class="row g-3">

                                        @php
                                            $engineering_positions = ['CPE', 'CE', 'ME', 'EE'];
                                        @endphp

                                        @foreach($engineering_positions as $position)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $position }}</label>

                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" id="{{ Str::slug($position, '') }}Select">
                                                        <option value="">Select</option>
                                                        @foreach($officers as $officer)
                                                            <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div> --}}

                                <!-- ====================== SSC TAB ====================== -->
                                <div class="tab-pane fade" id="tab-ssc">

                                    <!-- SSC Presidents -->
                                    <div class="row g-3">

                                        @php
                                            $ssc_positions = [
                                                'FCJ President', 'FCSIT President', 'FAS President',
                                                'FOT President', 'FOE President', 'FTVE President',
                                                'FHTM President', 'ROTC'
                                            ];
                                        @endphp

                                        @foreach($ssc_positions as $position)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $position }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" id="{{ Str::slug($position, '') }}Select">
                                                        <option value="">Select</option>
                                                        @foreach($students as $officer)
                                                            <option value="{{ $officer->profile_id }}">{{ $officer->last_name }},{{ $officer->first_name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>

                                    <hr class="my-4">

                                    <!-- Senators -->
                                    <h5 class="mb-3">Senators</h5>

                                    <div id="senatorContainer">
                                        <!-- Default Senator Row -->
                                        <div class="row g-3 senator-row mb-2">
                                            <div class="col-md-10">
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" name="senators[]">
                                                        <option value="">Select</option>
                                                        @foreach($students as $officer)
                                                            <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-md-2 d-flex">
                                                <button class="btn btn-success w-100 add-senator">
                                                    <i class="mdi mdi-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                                <!-- AMBASSADOR TAB -->
                                {{-- <div class="tab-pane fade" id="tab-ambassador">
                                    <div class="row g-3">

                                        @php
                                            $ambassador_positions = [
                                                'Head for Finance & Operations Division',
                                                'Head for Academic & Training Division',
                                                'Head for Research & Knowledge Management Division',
                                                'Head for Promotions & Media Division',
                                                'Head for Alumni Relations Division',
                                                'Head for Community Engagement & Partnership Division',
                                                'Head for Internationalization Division',
                                                'Head for Membership Recruitment & Retention Division'
                                            ];
                                        @endphp

                                        @foreach($ambassador_positions as $position)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $position }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" id="{{ Str::slug($position, '') }}Select">
                                                        <option value="">Select</option>
                                                        @foreach($officers as $officer)
                                                            <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div> --}}

                                <!-- ROTARY TAB -->
                                {{-- <div class="tab-pane fade" id="tab-rotary">
                                    <div class="row g-3">

                                        @php
                                            $rotary_positions = [
                                                'Membership Chair', 'Public Image Chair',
                                                'Learning Facilitator', 'Rotary Foundation Chair'
                                            ];
                                        @endphp

                                        @foreach($rotary_positions as $position)
                                            <div class="col-md-6">
                                                <label class="form-label">{{ $position }}</label>
                                                <div class="input-group">
                                                    <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                                                    <select class="form-select" id="{{ Str::slug($position, '') }}Select">
                                                        <option value="">Select</option>
                                                        @foreach($officers as $officer)
                                                            <option value="{{ $officer->id }}">{{ $officer->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        @endforeach

                                    </div>
                                </div> --}}

                            </div>



                        </div>

                    </div>
                </div>

                <!-- TABLE -->
                <div id="vertical-example" class="overflow-auto border rounded p-3 mt-3" style="height: 300px;">
                    <table class="table table-hover"></table>
                </div>

              </div>
            </div>

            <!-- ====================== FOOTER ====================== -->
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button class="btn btn-primary" id="btnSaveOfficers">Save</button>
            </div>

        </div>
    </div>
</div>


<!-- ====================== SENATOR JS ====================== -->
{{-- <script>
document.addEventListener("DOMContentLoaded", function() {

    const tabLinks = document.querySelectorAll('.nav-link[data-bs-toggle="tab"]');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Scrollable container of the modal body
    const scrollContainer = document.querySelector('.modal-body .overflow-auto');

    const observerOptions = {
        root: scrollContainer,
        rootMargin: '0px',
        threshold: 0.6 // 60% visible
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {

                const targetId = entry.target.id;
                const activeTab = document.querySelector(`.nav-link[data-bs-target="#${targetId}"]`);

                if (activeTab) {

                    // Activate bootstrap tab so underline moves correctly
                    const tab = new bootstrap.Tab(activeTab);
                    tab.show();

                    // Auto scroll tab bar to keep active tab visible
                    activeTab.scrollIntoView({
                        behavior: 'smooth',
                        inline: 'right',
                        block: 'nearest'
                    });
                }
            }
        });
    }, observerOptions);

    tabPanes.forEach(pane => observer.observe(pane));

});
</script> --}}


