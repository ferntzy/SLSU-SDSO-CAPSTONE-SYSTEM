<div class="modal fade" id="editAccountModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="staticBackdropLabel">EDIT PROFILE INFORMATION</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>

      <!-- Modal Body -->
      <div class="modal-body">
        <div id="accountdataeditmsg"></div>

        <form id="frmAccountDataEdit">
          @csrf
          <input type="hidden" id="hiddenProfileID" name="hiddenProfileID" value="0">

          <!-- Type and Profile Row -->
          <div class="row g-3 mb-3">
            <!-- Type Filter -->
            <div class="col-md-6">
              <label class="form-label">Select Type of User</label>
              <select id="typeFilter" class="form-select w-100">
                <option value="">All Types</option>
                <option value="student">Student</option>
                <option value="employee">Employee</option>
              </select>
            </div>

            <!-- Profile Dropdown -->
            <div class="col-md-6 position-relative">
              <label class="form-label">Select Profile</label>
              <input type="text" id="dropdownInput" class="form-control" placeholder="Select profile..." readonly>
              <input type="hidden" id="profile_id" name="profile_id">
              <span id="dropdownToggle" style="position:absolute; right:15px; top:40px; cursor:pointer;">
                <i class="mdi mdi-menu-down"></i>
              </span>

              <div id="dropdownList"
                  style="display:none; max-height:200px; overflow-y:auto; border:1px solid #ccc;
                         border-radius:6px; padding:5px; background:white; position:absolute; width:100%; z-index:99999;">
                @foreach ($user_profiles as $user_profile)
                  <div class="profile-row p-2"
                       data-id="{{ $user_profile->profile_id }}"
                       data-type="{{ strtolower($user_profile->type) }}"
                       style="cursor:pointer; border-bottom:1px solid #eee;">
                    {{ $user_profile->first_name }} {{ $user_profile->last_name }}
                  </div>
                @endforeach
              </div>
            </div>
          </div>

          <!-- ACCOUNT INFORMATION -->
          <div class="form text-dark mb-3">Account Information</div>
          <div class="row">
            <div class="col-md-6 mb-3">
              <select name="account_role" class="form-select" required>
                <option value="">Select Account Role</option>
                <option value="Student_Organization">Student Organization</option>
                <option value="SDSO_Head">SDSO Head</option>
                <option value="Faculty_Adviser">Faculty Adviser</option>
                <option value="VP_SAS">VP SAS</option>
                <option value="SAS_Director">SAS Director</option>
                <option value="BARGO">BARGO</option>
                <option value="admin">Admin</option>
              </select>
            </div>

            <div class="col-md-6 mb-3">
              <div class="input-group input-group-merge">
                <span class="input-group-text"><i class="mdi mdi-account-circle"></i></span>
                <input type="text" name="username" id="username" class="form-control" placeholder="Username" required>
                <small id="username-error" class="text-danger" style="display:none;">Username already exists</small>
              </div>
            </div>
          </div>

          <!-- PASSWORD -->
          <div class="row">
            <div class="col-md-6 mb-3">
              <label class="form-label">Password</label>
              <div class="input-group">
                <input type="password" name="password" class="form-control password-field">
                <span class="input-group-text toggle-password" style="cursor:pointer;">
                  <i class="bi bi-eye-slash"></i>
                </span>
              </div>
            </div>

            <div class="col-md-6 mb-3">
              <label class="form-label">Re-type Password</label>
              <div class="input-group">
                <input type="password" name="password_confirmation" class="form-control password-field">
                <span class="input-group-text toggle-password" style="cursor:pointer;">
                  <i class="bi bi-eye-slash"></i>
                </span>
              </div>
              <div id="password-match-error" class="text-danger mt-1" style="display:none; font-size:0.9rem;">
                Passwords do not match
              </div>
            </div>
          </div>

        </form>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        <button type="button" class="btn btn-warning" id="btnprofileupdate">Update</button>
      </div>

    </div>
  </div>
</div>
