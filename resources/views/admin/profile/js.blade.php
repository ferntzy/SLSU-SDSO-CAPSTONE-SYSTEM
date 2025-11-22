<script>
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  // add profile script
  
  $("#createProfileModal").on('shown.bs.modal', function(){
    $("#first_name").focus();
  })

  $(document).on("click", "#btnprofilesave", function(e){
    e.preventDefault();

    $.ajax({
        url: "{{ route('profiles.store') }}",
        method: "POST",
        data: $("#frmProfileData").serialize(),
        beforeSend:function(){
            $("#profiledatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnprofilesave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnprofilesave").prop("disabled", false);
            $("#profiledatamsg").html("<div class = 'alert alert-success'>Profile data saved.</div>");
            list();
            setTimeout(() => {
              $('.txt').val('');
              $("#first_name").focus();
            }, 1000);
        },

        error: function (response) {
            $("#btnprofilesave").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#profiledatamsg").html(errors);
        }
    });
  })

  // profile list

  function list(){
    $.ajax({
        url: "{{ route('profiles.list') }}",
        method: "POST",
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

  // edit script

  $("#editProfileModal").on('shown.bs.modal', function(){
    $("#first_name").focus();
  })


  $(document).on("click", '.btn-edit', function(e){
    let id = $(this).data('id');

      $.ajax({
          url: "",
          type: "GET",
          success: function(data) {
              $('input[name="last_name"]').val(data.last_name);
              $('input[name="first_name"]').val(data.first_name);
          }
      });
    });



</script>
