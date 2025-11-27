<!-- Vertically Centered Modal -->
      <div class="col-lg-4 col-md-6">
        <small class="text-light fw-medium">Vertically centered</small>
        <div class="mt-3">
          <!-- Button trigger modal -->
          <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCenter">
            Launch modal
          </button>

          <!-- Modal -->
          <div class="modal fade" id="modalCenter" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered" role="document">
              <div class="modal-content">
                <div class="modal-header">
                  <h4 class="modal-title" id="modalCenterTitle">Modal title</h4>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                  <div class="row">
                    <div class="col mb-4 mt-2">
                      <div class="form-floating form-floating-outline">
                        <input type="text" id="nameWithTitle" class="form-control" placeholder="Enter Name">
                        <label for="nameWithTitle">Name</label>
                      </div>
                    </div>
                  </div>
                  <div class="row g-2">
                    <div class="col mb-2">
                      <div class="form-floating form-floating-outline">
                        <input type="email" id="emailWithTitle" class="form-control" placeholder="xxxx@xxx.xx">
                        <label for="emailWithTitle">Email</label>
                      </div>
                    </div>
                    <div class="col mb-2">
                      <div class="form-floating form-floating-outline">
                        <input type="date" id="dobWithTitle" class="form-control">
                        <label for="dobWithTitle">DOB</label>
                      </div>
                    </div>
                  </div>
                </div>
                <div class="modal-footer">
                  <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
                  <button type="button" class="btn btn-primary">Save changes</button>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
