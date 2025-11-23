<script>
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  // add account script

  $("#createAccounteModal").on('show.bs.modal', function(){
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
      account_role = $("#account_role_student").val();
    }else{
      profile_id = $("#dropdownList-employee").val();
      account_role = $("#account_role_employee").val();
    }

    formData.append('profile_id', profile_id);
    formData.append('account_role', account_role);


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
            $("#accountdatamsg").html("<div class = 'alert alert-success'>Profile data saved.</div>");
            listuser();
            setTimeout(() => {
              $('.txt').val('');
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


  // =====================================================
  // EDIT ACCOUNT MODAL POPULATION
  // =====================================================
  $(document).on("click", '.btn-edit', function(e) {
      e.preventDefault();

      let id = $(this).data('id'); // encrypted user_id

      $.ajax({
          url: "{{ route('users.edit') }}",
          type: "POST",
          data: { id: id },
          beforeSend: function() {
              // Open modal
              $("#editAccountModal").modal('show');
              // Show loading message
              $("#accountdataeditmsg").html(
                  "<div class='alert alert-warning'><i class='spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>"
              );
          },
          success: function(data) {
              // Clear loading message
              $("#accountdataeditmsg").html("");

              // Set hidden ID
              $("#hiddenProfileID").val(data.user_id);

              // Profile dropdown
              $("#dropdownInput").val(data.profile.first_name + " " + data.profile.last_name);
              $("#profile_id").val(data.profile.profile_id);

              // Set account type in dropdown
              $("#typeFilter").val(data.profile.type.toLowerCase());

              // Account info
              $("select[name='account_role']").val(data.account_role);
              $("input[name='username']").val(data.username);

              // Passwords empty for security
              $("input[name='password']").val('');
              $("input[name='password_confirmation']").val('');

          },
          error: function(response) {
              var errors = response.responseJSON.errors;
              $("#accountdataeditmsg").html(errors);
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
