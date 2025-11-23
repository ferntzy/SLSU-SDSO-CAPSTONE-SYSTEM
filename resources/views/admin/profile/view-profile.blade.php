<div class="modal fade" id="viewProfileModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="viewProfileLabel" aria-hidden="true">
  <div class="modal-dialog modal-m modal-dialog-centered">
    <div class="modal-content p-4">
      <div class="modal-header text-center">
        <h1 class="modal-title fs-5 " id="staticBackdropLabel">PROFILE DETAILS</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <!-- Modal Body -->
      <div class="modal-body">
        <div id="profiledataviewmsg"></div>

        <!-- FULL NAME -->
        <div class="mb-5">
          <h6 class="text-uppercase mb-4"><i class="mdi mdi-account-outline me-2"></i>Full Name</h6>
          <p><strong>First Name:</strong> <span id="view_first_name"></span></p>
          <p><strong>Middle Name:</strong> <span id="view_middle_name"></span></p>
          <p><strong>Last Name:</strong> <span id="view_last_name"></span></p>
          <p><strong>Suffix:</strong> <span id="view_suffix"></span></p>
        </div>

        <!-- CONTACT INFO -->
        <div class="mb-5">
          <h6 class="text-uppercase mb-4"><i class="mdi mdi-email-outline me-2"></i>Contact Information</h6>
          <p><strong>Email:</strong> <span id="view_email"></span></p>
          <p><strong>Contact #:</strong> <span id="view_contact_number"></span></p>
          <p><strong>Address:</strong> <span id="view_address"></span></p>
        </div>

        <!-- USER DETAILS -->
        <div class="mb-5">
          <h6 class="text-uppercase mb-4"><i class="mdi mdi-account me-2"></i>User Details</h6>
          <p><strong>Sex:</strong> <span id="view_sex"></span></p>
          <p><strong>User Type:</strong> <span id="view_type"></span></p>
        </div>
      </div>

      <!-- Modal Footer -->
      <div class="modal-footer justify-content-center">
        <button type="button" class="btn btn-primary px-4" data-bs-dismiss="modal">DONE</button>
      </div>

    </div>
  </div>
</div>
