<div class="modal fade" id="createOrgModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-m modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white justify-content-center">
                <h5 class="modal-title text-center">ADD ORGANIZATION</h5>
                <button type="button" class="btn-close position-absolute end-0 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- BODY -->
            <div class="modal-body">
              <div id="organizationdatamsg"></div>
              <form id="frmOrganizationData">
                @csrf
                <input type="hidden" value="0" id="hiddenOrganizationID" name="hiddenOrganizationID">
                <input type="hidden" value="POST" id="hiddenOrganizationFlag">
                <!-- Organization Name -->
                <div class="mb-3">
                    <label class="form-label">Organization Name</label>
                    <input type="text" id="organization_name" name="organization_name" class="txt form-control" required>
                </div>

                <!-- Organization Type -->
                <div class="mb-4">
                    <label class="form-label">Organization Type</label>
                    <select name="organization_type" class="form-select" required>
                        <option value="">Select Organization Type</option>
                        <option>Academic Organization</option>
                        <option>Non-Academic Organization</option>
                    </select>
                </div>
                <!-- Adviser -->
                <div class="mb-3">
                  <label class="form-label">Organization Adviser</label>
                  <select name="adviser_id" id="adviser_id" class="form-select"  required>
                      <option value="">Select Adviser</option>
                      @foreach($advisers as $adviser)
                          <option value="{{ $adviser->user_id }}">
                              {{ $adviser->profile->first_name ?? " " }} {{ $adviser->profile->last_name ?? " "}}
                          </option>
                      @endforeach
                  </select>
                </div>

                <!-- Description -->
                <div class="mb-4">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="txt form-control" rows="3"></textarea>
                </div>

                <!-- Logo Upload (optional, won't be sent with serialize) -->
                <div class="mb-3">
                    <label class="form-label text-dark">Add Organization Logo</label>
                    <input type="file" id="logoInput" name="organization_logo" class="d-none" accept="image/*">
                    <button type="button" class="btn btn-primary btn-lg w-100" onclick="document.getElementById('logoInput').click()">
                        Choose File
                    </button>
                    <small id="logoFilename" class="text-muted d-block mt-2"></small>
                </div>

              </form>
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnorganizationsave">Save</button>
            </div>
        </div>
    </div>
</div>
