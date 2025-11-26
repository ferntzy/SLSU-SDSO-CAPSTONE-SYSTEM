<script>
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  // add account script

  $("#createAccountModal").on('show.bs.modal', function(){
    $("#hiddenAccountID").val(0);
    $("#hiddenAccountFlag").val("POST");

    $('.txt').val('');
  })

  $("#createAccountModal").on('shown.bs.modal', function(){
    $("#username").focus();
  })

  $(document).on("click", "#btnaccountsave", function(e){
    e.preventDefault();

    let flag = $("#hiddenAccountFlag").val();

    var form = $("#frmAccountData")[0];

    var formData = new FormData(form);
    let profile_id = 0;
    let account_role = "" ;
    if ($("#typeFilter").val() == "student"){
      profile_id = $("#dropdownList-student").val();
       account_role = "Student_Organization";
    }else{
      profile_id = $("#dropdownList-employee").val();
      account_role = $("#account_role_employee").val();
    }

    formData.append('profile_id', profile_id);
    formData.append('account_role', account_role);

       // FIX: send hiddenAccountID when updating
    if (flag !== "POST") {
        formData.append('hiddenAccountID', $("#hiddenAccountID").val());
    }

    $.ajax({
        url: (flag == "POST" ? "{{ route('users.store') }}" : "{{ route('users.update') }}"),
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        beforeSend:function(){
            $("#accountdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnaccountsave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnaccountsave").prop("disabled", false);
            if(flag === "POST"){
                $("#accountdatamsg").html("<div class = 'alert alert-success'>Account data saved.</div>");
            }else {
               $("#accountdatamsg").html("<div class = 'alert alert-success'>Account data update.</div>");

            }

            listuser();
            setTimeout(() => {

             if(flag === "POST"){
              $('.txt').val('');
             }

            $("#username").focus();
            }, 1000);
        },

        error: function (response) {
            $("#btnaccountsave").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#accountdatamsg").html(errors);
        }
    });
  })




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









// USER SEARCH LOGS ----------
// SEARCH on enter
$(document).on("keypress", "#searchAccountlogs", function (e) {
    if (e.which === 13) {
        e.preventDefault();
        loadLogs(1);
    }
});

// SEARCH button click
$(document).on("click", "#btnSearchlogs", function (e) {
    e.preventDefault();
    loadLogs(1);
});

// PAGINATION
$(document).on("click", ".pagination a", function (e) {
    e.preventDefault();

    let page = $(this).attr('href').split('page=')[1];
    loadLogs(page);
});

// MAIN AJAX FUNCTION
function loadLogs(page = 1) {

    let str = $("#searchAccountlogs").val(); // ← correct search input

    $.ajax({
        url: "{{ route('users.logs-list') }}?page=" + page,
        type: "GET",
        data: { str: str },
        beforeSend: function () {
            $("#logsList").html(
                "<div class='alert alert-warning'><i class='spinner-grow spinner-grow-sm'></i> Loading logs...</div>"
            );
        },
        success: function (data) {
            $("#logsList").html(data); // ← correct container
        },
        error: function (xhr) {
            console.error(xhr);
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



  // =====================================================
  // EDIT ACCOUNT MODAL POPULATION
  // =====================================================

  $(document).on("click", '.btn-edit', function(e){

      let id = $(this).data('id');
      $.ajax({
          url: "{{ route('users.edit') }}",
          type: "POST",
          data: {id},
          beforeSend:function(){
              $("#createAccountModal").modal('toggle');
              $("#accountdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
          },
          success: function(data) {
            $("#accountdatamsg").html("");
              // FULL NAME

            $('input[name="role"]').val(data.account_role);
            $('input[name="username"]').val(data.username);
            $('input[name="password"]').val(data.password);
            $('input[name="retype-password"]').val(data.password);


             $('select[name="typeFilter"]').val(data.profile.type).trigger("change");

           setTimeout(function() {

                // =============== PROFILE DROPDOWN ===============
                if (data.profile.type == "student") {

                  $("#dropdownList-student").val(data.profile.profile_id);
                 console.log(data);
                  $("#account_role_student").val(data.account_role);

                } else {
                  $("#dropdownList-employee").val(data.profile.profile_id);
                  $("#account_role_employee").val(data.account_role);
                }
            }, 150); // s

            $("#hiddenAccountID").val(id);
            $("#hiddenAccountFlag").val("UPDATE");

          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#data").html(errors);
          }
      });
    });





      //showing all profile based on the type----------------------------------

  // =================================================
  // DROPDOWN SELECT FOR FIRSTNAME + LASTNAME
  // =================================================

  $(document).ready(function () {
      $(document).on('change', "#typeFilter", function(){
          $("#dropdownList-student").hide();
          $("#dropdownList-student").hide();
          $("#dropdownList-employee").hide();
          $("#account_role_student").hide();
          $("#account_role_employee").hide();

          if ($(this).val() == 'student'){
            $("#dropdownList-student").show();
            $("#account_role_student").show();
          }else{
            $("#dropdownList-employee").show();
            $("#account_role_employee").show();
          }
      })

  });





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





</script>
