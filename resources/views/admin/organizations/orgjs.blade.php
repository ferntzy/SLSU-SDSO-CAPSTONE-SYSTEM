<!-- jQuery first (if your scripts need it) -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

<!-- Bootstrap Bundle (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$.ajaxSetup({
    headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
});

$(document).on("click", ".delete-org-btn", function(e) {
    e.preventDefault();

    let orgId = $(this).data("id");
    let orgName = $(this).data("name");
    let orgCard = $(this).closest(".col-md-6");

    if (!orgId) {
        console.error("Organization ID is missing!");
        return;
    }

    Swal.fire({
        title: 'Are you sure?',
        text: `Delete organization "${orgName}"? This action cannot be undone!`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Yes, delete it!'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: '/admin/organizations/' + orgId,
                type: 'DELETE',
                data: { _token: $('meta[name="csrf-token"]').attr('content') },
                success: function(response) {
                    if (response.success) {
                        orgCard.remove();
                        Swal.fire('Deleted!', response.message, 'success');
                    } else {
                        Swal.fire('Error!', response.message, 'error');
                    }
                },
                error: function(xhr) {
                    Swal.fire('Error!', 'Request failed: ' + xhr.responseText, 'error');
                }
            });
        }
    });
});
</script>
<script>document.addEventListener("DOMContentLoaded", function () {
    let successMessage = document.querySelector('meta[name="success-message"]');

    if (successMessage && successMessage.content.trim() !== "") {
        Swal.fire({
            title: 'Success!',
            text: successMessage.content,
            icon: 'success',
            confirmButtonColor: '#3085d6',
            confirmButtonText: 'OK'
        });
    }
});
</script>

<script>
$(document).ready(function () {
    // When a "View" button is clicked
    $(document).on('click', '.viewOrgBtn', function (e) {
        e.preventDefault();

        let orgId = $(this).data('id');

        if (!orgId) return;

        $.ajax({
            url: '/admin/organizations/' + orgId, // your route to fetch single org
            type: 'GET',
            dataType: 'json',
            success: function (org) {
                // Basic Info
                $('#orgName').text(org.organization_name || '—');
                $('#orgType').text(org.organization_type || '—');
                $('#orgDescription').text(org.description || '—');
                $('#orgMembers').text(org.members_count || '0');

                // Adviser
                let adviserName = org.adviser_user
                                  ? org.adviser_user.profile.first_name + ' ' + org.adviser_user.profile.last_name
                                  : '—';
                $('#orgAdvisor').text(adviserName);

                // Officer Info (fetch from user_profiles if officer table is empty)
                if (org.officer_user && org.officer_user.profile) {
                    $('#officer_id').text(org.officer_user.profile.first_name + ' ' + org.officer_user.profile.last_name);
                    $('#contact_number').text(org.officer_user.profile.contact_number || '—');
                    $('#contact_email').text(org.officer_user.profile.email || '—');
                } else {
                    $('#officer_id').text('—');
                    $('#contact_number').text('—');
                    $('#contact_email').text('—');
                }

                // Created At
                $('#orgCreatedAt').text(org.created_at || '—');

                // Logo
                if (org.logo) {
                    $('#orgLogo').attr('src', '/storage/' + org.logo);
                } else {
                    $('#orgLogo').attr('src', '/images/default-logo.png');
                }

                // Status Badge
                let statusBadge = $('#orgStatus');
                statusBadge.text(org.status || '—');
                statusBadge.removeClass('bg-label-success bg-label-danger');
                if (org.status === 'Active') {
                    statusBadge.addClass('bg-label-success');
                } else if (org.status === 'Inactive') {
                    statusBadge.addClass('bg-label-danger');
                }

                // Show the modal
                let modal = new bootstrap.Modal(document.getElementById('orgDetailsModal'));
                modal.show();
            },
            error: function (xhr, status, error) {
                console.error('Error fetching organization:', error);
                Swal.fire('Error', 'Failed to fetch organization details', 'error');
            }
        });
    });
});
</script>

