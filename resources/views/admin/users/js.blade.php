<script>
 // ==================== CREATE / EDIT ACCOUNT MODAL ====================

$(document).ready(function() {

  // -------- OPEN CREATE MODAL --------
  $(document).on("click", "#btnCreateAccount", function() {
      $("#hiddenAccountFlag").val("POST");
      $("#hiddenAccountID").val(0);

      // Clear all fields
      $('.txt').val('');
      $("#typeFilter").val('').trigger('change');
      $("#accountdatamsg").html('');
      $("#username").prop('disabled', false);
      $("#password, #retype-password").prop('required', true);

      $("#createAccountModal").modal('show');
      setTimeout(() => $("#username").focus(), 200);
  });

// -------- OPEN EDIT MODAL --------
$(document).on("click", ".btn-edit", function() {
    let id = $(this).data('id');

    $("#hiddenAccountFlag").val("UPDATE");
    $("#hiddenAccountID").val(id);

    $("#accountdatamsg").html("<div class='alert alert-warning'><i class='spinner-grow spinner-grow-sm'></i> Loading...</div>");
    $("#createAccountModal").modal('show');

    $.ajax({
        url: "{{ route('users.edit') }}",
        type: "POST",
        data: { id: id },
        success: function(data) {
            $("#accountdatamsg").html("");

            // Username readonly
            $("#username").val(data.username).prop('readonly', true);

            // Clear passwords
            $("#password, #retype-password").val('').prop('required', false);

            // Type of User readonly but still submitted
            $('select[name="typeFilter"]').val(data.profile.type).prop('disabled', true);
            $("#typeFilter_hidden").val(data.profile.type);

            let profile_id = data.profile.profile_id;

            if (data.profile.type === "student") {

                $("#dropdownList-student").prop('disabled', true).show();
                $("#dropdownList-employee").hide();

                // Auto-select student profile
                $("#dropdownList-student").val(profile_id);
                $("#profile_hidden").val(profile_id);

                // Show correct account role
                $("#account_role_student").val(data.account_role).prop('disabled', false).show();
                $("#account_role_employee").hide();

            } else {

                $("#dropdownList-employee").prop('disabled', true).show();
                $("#dropdownList-student").hide();

                // Auto-select employee profile
                $("#dropdownList-employee").val(profile_id);

                // Hidden input for backend submission
                $("#profile_hidden").val(profile_id);

                // Show correct account role
                $("#account_role_employee").val(data.account_role).prop('disabled', false).show();
                $("#account_role_student").hide();
            }
        },
        error: function() {
            $("#accountdatamsg").html('<div class="alert alert-danger">Unable to load user data.</div>');
        }
    });
});




  // -------- SAVE ACCOUNT (CREATE / UPDATE) --------
  $(document).on("click", "#btnaccountsave", function(e) {
      e.preventDefault();

      let flag = $("#hiddenAccountFlag").val();
      var form = $("#frmAccountData")[0];
      var formData = new FormData(form);

      // Determine profile & role
      let profile_id = 0;
      let account_role = "";
      if ($("#typeFilter").val() === "student") {
          profile_id = $("#dropdownList-student").val();
          account_role = "Student_Organization";
      } else {
          profile_id = $("#dropdownList-employee").val();
          account_role = $("#account_role_employee").val();
      }
      formData.set('profile_id', profile_id);
      formData.set('account_role', account_role);

      // Include hidden ID only for update
      if (flag === "UPDATE") {
          formData.set('hiddenAccountID', $("#hiddenAccountID").val());
      }

      $.ajax({
          url: flag === "POST" ? "{{ route('users.store') }}" : "{{ route('users.update') }}",
          method: "POST",
          data: formData,
          processData: false,
          contentType: false,
          beforeSend: function() {
              $("#accountdatamsg").html("<div class='alert alert-warning'><i class='spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
              $("#btnaccountsave").prop("disabled", true);
          },
          success: function(data) {
              $("#btnaccountsave").prop("disabled", false);
              $("#accountdatamsg").html("<div class='alert alert-success'>"+ (flag === "POST" ? "Account created." : "Account updated.") +"</div>");

              listuser(); // refresh account list

              setTimeout(() => {
                  if (flag === "POST") {
                      $('.txt').val('');
                      $("#username").prop('disabled', false);
                      $("#password, #retype-password").prop('required', true);
                  }
                  $("#username").focus();
              }, 1000);
          },
          error: function(response) {
              $("#btnaccountsave").prop("disabled", false);
              var errors = response.responseJSON.errors;
              $("#accountdatamsg").html(errors);
          }
      });
  });

  // // -------- REFRESH PAGE ON MODAL CLOSE --------
  // $('#createAccountModal').on('hidden.bs.modal', function () {
  //     location.reload(); // reload page after closing modal
  // });





  // -------- TOGGLE DROPDOWNS BASED ON TYPE --------
  $(document).on('change', "#typeFilter", function() {
      $("#dropdownList-student, #dropdownList-employee, #account_role_student, #account_role_employee").hide();

      if ($(this).val() === 'student') {
          $("#dropdownList-student, #account_role_student").show();
      } else if ($(this).val() === 'employee') {
          $("#dropdownList-employee, #account_role_employee").show();
      }
  });

});





// SEARCH ACCOUNT ----------

  $(document).on("keypress", "#searchAccount", function(e) {
      if (e.which === 13) { // 13 = Enter key
          e.preventDefault(); // Prevent form submission
          listuser();             // Call your AJAX list function
      }
  });

  // Trigger list() on button click
  $(document).on("click", "#btnSearchUser", function(e) {
      e.preventDefault();
      listuser();                 // Call your AJAX list function
  });

  // account list ---------------
  function listuser(){
    let str = $("#searchAccount").val();
    $.ajax({
        url: "{{ route('users.list') }}",
        method: "POST",
        data: {str},
        beforeSend:function(){
            $("#accountlists").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#accountlists").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#data").html(errors);
        }
    });
  }

















// view profile modal script
    $(document).on("click", '.btn-view', function(e){

        let id =$(this).data('id');

        $.ajax({
          url: "{{ route('users.view') }}",
          type: "POST",
          data: {id},
          beforeSend:function(){
              $("#viewAccountModal").modal('toggle');
              $("#accountdataviewmsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
          },
          success: function(data) {
            console.log(data);
            $("#accountdataviewmsg").html("");

               // <!-- USER DETAILS -->
            $('#view_username').html(data.username);
            $('#view_type').html(data.profile.type);
            $('#view_role').html(data.account_role);
            $('#view_date').html(new Date(data.created_at).toISOString().split('T')[0]);
            $('#view_time').html(new Date(data.created_at).toLocaleTimeString('en-US', { hour12: true }));
              // ACCOUNT INFO
            $('#view_first_name').html(data.profile.first_name);
            $('#view_last_name').html(data.profile.last_name);
            $('#view_middle_name').html(data.profile.middle_name);
            $('#view_suffix').html(data.profile.suffix);
            $('#view_sex').html(data.profile.sex);

            // CONTACT INFO
            $('#view_email').html(data.profile.email);
            $('#view_contact_number').html(data.profile.contact_number);
            $('#view_address').html(data.profile.address);

            $('#viewProfileModal').modal({
                backdrop: true,   // clicking outside closes the modal
                keyboard: true
            });

          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#data").html(errors);
          }

        })

    });



  // // =====================================================
  // // EDIT ACCOUNT MODAL POPULATION
  // // =====================================================

  // $(document).on("click", '.btn-edit', function(e){

  //     let id = $(this).data('id');
  //     $.ajax({
  //         url: "{{ route('users.edit') }}",
  //         type: "POST",
  //         data: {id},
  //         beforeSend:function(){
  //             $("#createAccountModal").modal('toggle');
  //             $("#accountdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
  //         },
  //         success: function(data) {
  //           $("#accountdatamsg").html("");
  //             // FULL NAME

  //           $('input[name="role"]').val(data.account_role);
  //           $('input[name="username"]').val(data.username);
  //           $('input[name="password"]').val(data.password);
  //           $('input[name="retype-password"]').val(data.password);


  //            $('select[name="typeFilter"]').val(data.profile.type).trigger("change");

  //          setTimeout(function() {

  //               // =============== PROFILE DROPDOWN ===============
  //               if (data.profile.type == "student") {

  //                 $("#dropdownList-student").val(data.profile.profile_id);
  //                console.log(data);
  //                 $("#account_role_student").val(data.account_role);

  //               } else {
  //                 $("#dropdownList-employee").val(data.profile.profile_id);
  //                 $("#account_role_employee").val(data.account_role);
  //               }
  //           }, 150); // s

  //           $("#hiddenAccountID").val(id);
  //           $("#hiddenAccountFlag").val("UPDATE");

  //         },
  //         error: function (response) {
  //             var errors = response.responseJSON.errors;
  //             $("#data").html(errors);
  //         }
  //     });
  //   });







      //showing all profile based on the type----------------------------------

  // =================================================
  // DROPDOWN SELECT FOR FIRSTNAME + LASTNAME
  // =================================================

  // $(document).ready(function () {
  //     $(document).on('change', "#typeFilter", function(){
  //         $("#dropdownList-student").hide();
  //         $("#dropdownList-student").hide();
  //         $("#dropdownList-employee").hide();
  //         $("#account_role_student").hide();
  //         $("#account_role_employee").hide();

  //         if ($(this).val() == 'student'){
  //           $("#dropdownList-student").show();
  //           $("#account_role_student").show();
  //         }else{
  //           $("#dropdownList-employee").show();
  //           $("#account_role_employee").show();
  //         }
  //     })

  // });





  /// DELETE ACCOUNT

  $(document).on("click", ".btn-delete", function(e){
        e.preventDefault();

        let button = $(this);
        let url = button.data('url');

        Swal.fire({
            title: 'Are you sure?',
            text: `Delete this profile ?`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if(result.isConfirmed){
                $.ajax({
                    url: url,
                    type: "DELETE",
                    data: {
                        _token: "{{ csrf_token() }}"
                    },
                    success: function(response) {
                        if(response.success) {
                            button.closest('tr').fadeOut();
                            Swal.fire(
                                'Deleted!',
                                'Account has been deleted.',
                                'success'
                            );
                        } else if(response.errors) {
                            $('body').prepend(response.errors);
                        }
                    },
                    error: function (response) {
                        var errors = response.responseJSON.errors;
                        $("#data").html(errors);
                    }
                });
            }
        });
    });














// // =================================================
// // USERNAME TRAP KUNG ALREADY EXISTS


//   let typingTimer;

//   $(document).on("keyup", "#username", function () {

//       clearTimeout(typingTimer);

//       let username = $(this).val();

//       if (username.length === 0) {
//           $("#username-error").hide();
//           $("#username").removeClass("is-invalid");
//           return;
//       }
//       typingTimer = setTimeout(function () {

//           $.ajax({
//               url: "/user/check-username",
//               type: "POST",
//               data: {
//                   username: username,
//                   _token: $('meta[name="csrf-token"]').attr("content")
//               },
//               success: function (response) {
//                   if (response.exists) {

//                       $("#username-error").show();
//                       $("#username").addClass("is-invalid");
//                   } else {
//                       $("#username-error").hide();
//                       $("#username").removeClass("is-invalid");
//                   }
//               }
//           });

//       }, 300);
//   });




      // =================================================
    // PASSWORD TOGGLE ON ug OF
    $(document).on("click", ".toggle-password", function () {

        const passwordField = $(this).closest('.input-group').find('.password-field');
        const icon = $(this).find('i');

        if (passwordField.attr("type") === "password") {
            passwordField.attr("type", "text");
            icon.removeClass("mdi-eye-off").addClass("mdi-eye");
        } else {
            passwordField.attr("type", "password");
            icon.removeClass("mdi-eye").addClass("mdi-eye-off");
        }
    });

      // =================================================
    // PASSWORD Live match checking

    $(document).on("keyup", "input[name='password'], input[name='password_confirmation']", function () {

        let password = $("input[name='password']").val();
        let confirmPassword = $("input[name='password_confirmation']").val();

        // If confirm password is empty → hide error
        if (confirmPassword.length === 0) {
            $("#password-match-error").hide();
            return;
        }

        // Check if they match
        if (password !== confirmPassword) {
            $("#password-match-error").show().text("Passwords do not match");
        } else {
            $("#password-match-error").hide();
        }
    });






///////////////////////////////////////////////////////////////////////////////////////////////////////


 document.addEventListener('DOMContentLoaded', function () {
    initHoverTooltip(); // Initialize tooltip on page load
});

////////////////////////////

// Hover tooltip function (call this after AJAX too)
function initHoverTooltip() {
    let tooltip = document.querySelector('.live-tooltip');

    if (!tooltip) {
        tooltip = document.createElement('div');
        tooltip.className = 'live-tooltip';
        document.body.appendChild(tooltip);
    }

    document.querySelectorAll('.hover-text').forEach(el => {
        // Remove old listeners if any
        el.removeEventListener('mouseenter', el._enterHandler);
        el.removeEventListener('mousemove', el._moveHandler);
        el.removeEventListener('mouseleave', el._leaveHandler);

        const enterHandler = function() {
            tooltip.innerText = this.dataset.fulltext;
            tooltip.style.display = 'block';
        };
        const moveHandler = function(e) {
            tooltip.style.top = (e.clientY - 40) + 'px';
            tooltip.style.left = (e.clientX + 10) + 'px';
        };
        const leaveHandler = function() {
            tooltip.style.display = 'none';
        };

        el.addEventListener('mouseenter', enterHandler);
        el.addEventListener('mousemove', moveHandler);
        el.addEventListener('mouseleave', leaveHandler);

        // Save references to remove later
        el._enterHandler = enterHandler;
        el._moveHandler = moveHandler;
        el._leaveHandler = leaveHandler;
    });
}

////////////////////////////

document.addEventListener("alpine:init", () => {
    Alpine.data("logsTable", () => ({
        selectedIds: [],
        allSelected: false,
        logIds: [],
        init() {
            this.refreshLogIds();
        },
        refreshLogIds() {
            this.logIds = [...document.querySelectorAll('.row-checkbox')].map(cb => parseInt(cb.value));
            this.updateAllCheckbox();
        },
        toggleSelect(id) {
            if (this.selectedIds.includes(id)) {
                this.selectedIds = this.selectedIds.filter(x => x !== id);
            } else {
                this.selectedIds.push(id);
            }
            this.updateAllCheckbox();
        },
        toggleAll() {
            if (this.allSelected) {
                this.selectedIds = [];
            } else {
                this.selectedIds = [...this.logIds];
            }
            this.allSelected = !this.allSelected;
        },
        updateAllCheckbox() {
            this.allSelected = this.selectedIds.length === this.logIds.length;
        },
        async bulkDelete() {
            if (this.selectedIds.length === 0) return;

            const result = await Swal.fire({
                title: `Delete ${this.selectedIds.length} log(s)?`,
                text: "This action cannot be undone!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Yes, delete them!'
            });

            if (result.isConfirmed) {
                const response = await fetch("{{ route('logs.bulk-delete') }}", {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({ ids: this.selectedIds })
                });

                const data = await response.json();
                if (data.success) {
                    this.selectedIds.forEach(id => {
                        const tr = document.querySelector(`input[value="${id}"]`).closest("tr");
                        tr.remove();
                    });
                    Swal.fire("Deleted!", "Selected logs have been removed.", "success");
                    this.selectedIds = [];
                    this.allSelected = false;
                    this.refreshLogIds();
                }
            }
        }
    }));
});

////////////////////////////

// AJAX search & pagination
function loadLogs(page = 1){
    let str = $('#searchAccountLogs').val();

    $.ajax({
        url: "{{ route('users.logs-list') }}?page=" + page,
        type: "GET",
        data: { str: str },
        beforeSend: function(){
            $('#logsContainer tbody').html(
                `<tr>
                    <td colspan="7" class="text-center">
                        <i class="spinner-grow spinner-grow-sm"></i> Loading logs...
                    </td>
                </tr>`
            );
        },
        success: function(data){
            $('#logsContainer').html(data);

            // Reinitialize Alpine
            if(typeof Alpine !== 'undefined'){
                Alpine.initTree(document.getElementById('logsContainer'));
                const component = Alpine.getComponent(document.querySelector('[x-data="logsTable()"]'));
                if(component) component.refreshLogIds();
            }

            // Reinitialize hover tooltip
            initHoverTooltip();
        },
        error: function(xhr){
            console.error(xhr);
            $('#logsContainer tbody').html(
                `<tr>
                    <td colspan="7" class="text-center text-danger">Unable to load logs.</td>
                </tr>`
            );
        }
    });
}

$(document).ready(function(){
    // Search on Enter
    $(document).on('keypress', '#searchAccountLogs', function(e){
        if(e.which === 13){
            e.preventDefault();
            loadLogs(1);
        }
    });

    // Search button click
    $(document).on('click', '#btnSearchLogs', function(e){
        e.preventDefault();
        loadLogs(1);
    });

    // Pagination click
    $(document).on('click', '.pagination a', function(e){
        e.preventDefault();
        let page = $(this).attr('href').split('page=')[1];
        loadLogs(page);
    });
});




</script>
