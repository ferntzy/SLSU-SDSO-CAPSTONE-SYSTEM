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

    $.ajax({
        url: (flag == "POST" ? "{{ route('users.store') }}" : "{{ route('users.update') }}"),
        method: "POST",
        data: $("#frmAccountData").serialize(),
        beforeSend:function(){
            $("#accountdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnaccountsave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnaccounsave").prop("disabled", false);
            $("#accoundatamsg").html("<div class = 'alert alert-success'>Profile data saved.</div>");
            list();
            setTimeout(() => {
              $('.txt').val('');
              $("#username").focus();
            }, 1000);
        },

        error: function (response) {
            $("#btnaccounsave").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#accoundatamsg").html(errors);
        }
    });
  })



  $(document).on("click", "#btnaccountupdate", function(e){
    e.preventDefault();
    $.ajax({
        url: "{{ route('users.store') }}",
        method: "POST",
        data: $("#frmAccountData").serialize(),
        beforeSend:function(){
            $("#accoundatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnaccounsave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnaccounsave").prop("disabled", false);
            $("#accoundatamsg").html("<div class = 'alert alert-success'>Account data saved.</div>");
            list();
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



  // profile list

  function list(){
    $.ajax({
        url: "{{ route('users.list') }}",
        method: "POST",
        beforeSend:function(){
            $("#accountlist").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#accountlist").html(data);
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
        $("#account_role_emloyee").hide();
        if ($(this).val() == 'student'){
          $("#dropdownList-student").show();
          $("#account_role_student").show();
        }else{
          $("#dropdownList-employee").show();
          $("#account_role_employee").show();
        }
    })

});




















// =====================================================
// SEARCH PROFILES (AUTO FILTER, SEARCH ALL COLUMNS)
// =====================================================

// $(document).ready(function () {

//     $("#searchAccount").on("keyup", function () {

//         let searchValue = $(this).val().toLowerCase();

//         // Loop through all table rows
//         $("table tbody tr").filter(function () {

//             // Check if ANY cell in the row contains the text
//             let rowText = $(this).text().toLowerCase();

//             $(this).toggle(rowText.indexOf(searchValue) > -1);

//         });

//     });

// });



</script>
