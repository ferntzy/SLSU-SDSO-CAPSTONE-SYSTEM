<script>
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  // add profile script

  $("#createProfileModal").on('show.bs.modal', function(){
    $("#hiddenProfileID").val(0);
    $("#hiddenProfileFlag").val("POST");

    $('.txt').val('');
  })

  $("#createProfileModal").on('shown.bs.modal', function(){
    $("#first_name").focus();
  })

  $(document).on("click", "#btnprofilesave", function(e){
    e.preventDefault();
    let flag = $("#hiddenProfileFlag").val();


    $.ajax({
        url: (flag == "POST" ? "{{ route('profiles.store') }}" : "{{ route('profiles.update') }}"),
        method: "POST",
        data: $("#frmProfileData").serialize(),
        beforeSend:function(){
            $("#profiledatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnprofilesave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnprofilesave").prop("disabled", false);
            if(flag === "POST"){
              $("#profiledatamsg").html("<div class = 'alert alert-success'>Profile data saved.</div>");
            }else {
              $("#profiledatamsg").html("<div class = 'alert alert-success'>Profile data updated.</div>");
            }
            profilelist();
            setTimeout(() => {
              if(flag === "POST"){
                $('.txt').val('');
              }
              $("#first_name").focus();
            }, 1000);
        },

        error: function (response) {
            $("#btnprofilesave").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#profiledatamsg").html(errors);
        }
    });
  });





// SEARCH PROFILE ----------

  $(document).on("keypress", "#searchProfile", function(e) {
      if (e.which === 13) { // 13 = Enter key
          e.preventDefault(); // Prevent form submission
          profilelist();             // Call your AJAX list function
      }
  });

  // Trigger list() on button click
  $(document).on("click", "#btnSearchProfile", function(e) {
      e.preventDefault();
      profilelist();                 // Call your AJAX list function
  });

  // profile list

  function profilelist(){
     let str = $("#searchProfile").val();
    $.ajax({
        url: "{{ route('profiles.list') }}",
        method: "POST",
        data: { str},
        beforeSend:function(){
            $("#profilelist").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#profilelist").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#data").html(errors);
        }
    });
  }





  // populate edit form script
  $(document).on("click", '.btn-edit', function(e){

      let id = $(this).data('id');
      $.ajax({
          url: "{{ route('profiles.edit') }}",
          type: "POST",
          data: {id},
          beforeSend:function(){
              $("#createProfileModal").modal('toggle');
              $("#profiledatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
          },
          success: function(data) {
            $("#profiledatamsg").html("");
              // FULL NAME
            $('input[name="first_name"]').val(data.first_name);
            $('input[name="last_name"]').val(data.last_name);
            $('input[name="middle_name"]').val(data.middle_name);
            $('select[name="suffix"]').val(data.suffix);

            // CONTACT INFO
            $('input[name="email"]').val(data.email);
            $('input[name="contact_number"]').val(data.contact_number);

            // ADDRESS
            $('input[name="address"]').val(data.address);

            // SEX + TYPE
            $('select[name="sex"]').val(data.sex);
            $('select[name="type"]').val(data.type);

            $("#hiddenProfileID").val(id);
            $("#hiddenProfileFlag").val("UPDATE");

          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#data").html(errors);
          }
      });
    });

    // view profile modal script
    $(document).on("click", '.btn-view', function(e){

        let id =$(this).data('id');

        $.ajax({
          url: "{{ route('profiles.view') }}",
          type: "POST",
          data: {id},
          beforeSend:function(){
              $("#viewProfileModal").modal('toggle');
              $("#profiledataviewmsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
          },
          success: function(data) {
            console.log(data);
            $("#profiledataviewmsg").html("");
              // FULL NAME
            $('#view_first_name').html(data.first_name);
            $('#view_last_name').html(data.last_name);
            $('#view_middle_name').html(data.middle_name);
            $('#view_suffix').html(data.suffix);

            // CONTACT INFO
            $('#view_email').html(data.email);
            $('#view_contact_number').html(data.contact_number);

            // ADDRESS
            $('#view_address').html(data.address);

            // SEX + TYPE
            $('#view_sex').html(data.sex);
            $('#view_type').html(data.type);

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
                                'User profile has been deleted.',
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




</script>
