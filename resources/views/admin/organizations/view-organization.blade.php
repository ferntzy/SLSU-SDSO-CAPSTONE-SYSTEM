<div class="modal fade" id="orgDetailsModal" tabindex="-1" aria-labelledby="orgDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">

            <!-- HEADER -->
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Organization Details</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <!-- BODY -->
            <div class="modal-body">

                <!-- Organization Logo -->
                <div class="text-center mb-3">
                    <img id="orgLogo" src="" alt="Organization Logo" class="img-fluid rounded" style="max-height: 120px;">
                </div>

                <!-- Name & Type -->
                <h4 id="orgName" class="fw-bold text-primary mb-2"></h4>
                <p id="orgType" class="text-muted mb-3"></p>

                <!-- Description -->
                <p><strong>Description:</strong></p>
                <p id="orgDescription" class="mb-4"></p>

                <!-- Members & Adviser -->
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <p><strong>Members:</strong> <span id="orgMembers">—</span></p>
                    </div>
                    <div class="col-md-6 mb-3">
                        <p><strong>Adviser:</strong> <span id="orgAdvisor">—</span></p>
                    </div>
                </div>

                <!-- Created Date -->
                <p><strong>Created At:</strong> <span id="orgCreatedAt">—</span></p>

                <hr>

                <!-- Officers Section -->
                <h5 class="fw-bold mb-3">Select Student Organization Officer and Adviser</h5>

                <div class="row">
                    <div class="col-md-4 mb-2">
                        <p><strong>Officer:</strong> <span id="officer_id">—</span></p>
                    </div>
                    <div class="col-md-4 mb-2">
                        <p><strong>Contact:</strong> <span id="contact_number">—</span></p>
                    </div>
                    <div class="col-md-4 mb-2">
                        <p><strong>Email:</strong> <span id="contact_email">—</span></p>
                    </div>
                </div>

                <hr>

                <!-- Status -->
                <p>
                    <strong>Status:</strong>
                    <span id="orgStatus" class="badge">—</span>
                </p>

            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>

        </div>
    </div>
</div>
