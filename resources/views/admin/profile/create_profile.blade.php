<div class="modal fade" id="createProfileModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered ">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">PROFILE REGISTRATION</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <div id="profiledatamsg"></div>
        <form id = "frmProfileData">
          @csrf
          <input type = "text" value = "0" id = "hiddenProfileID" name = "hiddenProfileID" class="d-none">
          <input type = "text" value = "POST" id = "hiddenProfileFlag" class="d-none" >
          <!-- FULL NAME -->
          <div class="form text-dark mb-3">FULL NAME</div>

          <div class="row">

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" id="first_name" name="first_name" class="txt form-control" placeholder="First Name">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" name="last_name" class="txt form-control" placeholder="Last Name">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <input type="text" name="middle_name" class="txt form-control" placeholder="Middle Name">
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-outline"></i></span>
                <select name="suffix" class="txt form-control">
                  <option value="">(e.g. JR, SR, III)</option>
                  <option value="JR">Jr</option>
                  <option value="SR">Sr</option>
                  <option value="II">II</option>
                  <option value="III">III</option>
                  <option value="IV">IV</option>
                  <option value="V">V</option>
                  <option value="VI">VI</option>
                  <option value="VII">VII</option>
                  <option value="VIII">VIII</option>
                  <option value="IX">IX</option>
                  <option value="X">X</option>
                </select>
              </div>
            </div>

          </div>

          <!-- CONTACT INFORMATION -->
          <div class="form text-dark mb-3">CONTACT INFORMATION</div>

          <div class="row">
            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-email-outline"></i></span>
                <input type="text" name="email" class="txt form-control" placeholder="Email">
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-phone"></i></span>
                <input type="text" name="contact_number" class="txt form-control" placeholder="Contact Number">
              </div>
            </div>
          </div>

          <!-- ADDRESS -->
          <div class="mb-4">
            <div class="input-group input-group-merge">
              <span class="input-group-text"><i class="mdi mdi-map-marker-outline"></i></span>
              <input type="text" name="address" class="txt form-control" placeholder="Address">
            </div>
          </div>

          <!-- SEX + TYPE -->
          <div class="row">

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-gender-male-female"></i></span>
                <select name="sex" class="txt form-control">
                  <option value="">Select Sex</option>
                  <option value="Male">Male</option>
                  <option value="Female">Female</option>
                </select>
              </div>
            </div>

            <div class="col-md-6 mb-4">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-badge-horizontal-outline"></i></span>
                <select name="type" class="txt form-control">
                  <option value="">Select User Profile Type</option>
                  <option value="student">Student</option>
                  <option value="employee">Employee</option>
                </select>
              </div>
            </div>

          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id = "btnprofilesave">Save</button>
      </div>
    </div>
  </div>
</div>
