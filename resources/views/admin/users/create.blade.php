<div class="modal fade" id="createAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">

      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">ACCOUNT REGISTRATION</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <div class="modal-body">
        <div id="accountdatamsg"></div>

        <form id="frmAccountData">
          @csrf
          <input type = "text" value = "0" id = "hiddenAccountID" name = "hiddenAccountID" class ="d-none">
          <input type = "text" value = "POST" id = "hiddenAccountFlag" class ="d-none">

          <!-- Horizontal Row: Type Filter, Profile, Account Role -->
          <div class="row g-3 mb-3">

            <!-- Type Filter -->
            <div class="col-md-4">
              <label class="form-label">Select Type of User</label>
              <select id="typeFilter" name= "typeFilter" class="form-select w-100 txt">
                <option value="">All Types</option>
                <option value="student">Student</option>
                <option value="employee">Employee</option>
              </select>
            </div>

            <!-- ACcounnt Dropdown -->
            <div class="col-md-4 txt">
              <label class="form-label ">Select Profile</label>
              <select name = "profile_id" id="dropdownList-student" class="form-select txt" style="display:none;" >
                <option value="0" >Please select from the list</option>
                @foreach ($user_profiles_student as $user_profile)
                  <option value="{{ $user_profile->profile_id }}">{{ $user_profile->last_name }}, {{ $user_profile->first_name }}</option>
                @endforeach
              </select>

              <select name = "profile_id" id="dropdownList-employee" class="form-select txt" style="display:none;">
                <option value="0">Please select from the list</option>
                @foreach ($user_profiles_employee as $user_profile)
                  <option value="{{ $user_profile->profile_id }}">{{ $user_profile->last_name }}, {{ $user_profile->first_name }}</option>
                @endforeach
              </select>
            </div>

            <!-- Account Role -->
            <div class="col-md-4">
              <label class="form-label">Select Account Role</label>
              <select name="account_role" id="account_role_employee" class="form-select txt" style="display:none;" required>
                <option value="">Select Account Role</option>
                <option value="SDSO_Head">SDSO Head</option>
                <option value="Faculty_Adviser">Faculty Adviser</option>
                <option value="VP_SAS">VP SAS</option>
                <option value="SAS_Director">SAS Director</option>
                <option value="BARGO">BARGO</option>
                <option value="admin">Admin</option>
              </select>

              <select name="account_role" id="account_role_student" class="form-select txt" style="display:none;" required>

                <option value="Student_Organization">Student Organization</option>
              </select>
            </div>

          </div>

       <!-- ACCOUNT INFORMATION -->
          <div class="form text-dark mb-3">ACCOUNT INFORMATION</div>

            <div class="row">

              <!-- Username (same width as password fields) -->
              <div class="col-md-6 mb-3">
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                  <input type="text" name="username" id="username" class="form-control txt" placeholder="Username" required>

                </div>
                <small id="username-error" class="text-danger mt-1" style="display:none;">
                  Username already exists
                </small>
              </div>

               <div class="col-md-6 mb-3">
              </div>

              <!-- Password (left) -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Password</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="mdi mdi-lock"></i></span>
                  <input type="password" name="password" id="password" class="form-control password-field txt" required>
                  <span class="input-group-text toggle-password" style="cursor:pointer;">
                    <i class="mdi mdi-eye-off" id="togglePasswordIcon"></i>
                  </span>
                </div>
              </div>

              <!-- Re-type Password (right) -->
              <div class="col-md-6 mb-3">
                <label class="form-label">Re-type Password</label>
                <div class="input-group input-group-merge">
                  <span class="input-group-text"><i class="mdi mdi-lock-alert-outline"></i></span>
                  <input type="password" name="password_confirmation" id="retype-password" class="form-control password-field txt" required>
                  <span class="input-group-text toggle-password" style="cursor:pointer;">
                    <i class="mdi mdi-eye-off"></i>
                  </span>
                </div>
                <div id="password-match-error" class="text-danger mt-1" style="display:none; font-size: 0.9rem;">
                  Passwords do not match
                </div>
              </div>

            </div>


        </form>
      </div>

      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-primary" id="btnaccountsave">Save</button>
      </div>

    </div>
  </div>
</div>
