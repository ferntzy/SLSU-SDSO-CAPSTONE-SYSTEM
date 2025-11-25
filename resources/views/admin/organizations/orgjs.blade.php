<script>
  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  // add

  $("#logoInput").on('change', function(){
      $("#logoFilename").text(this.files[0]?.name || '');
  });


  $("#createOrgModal").on('show.bs.modal', function(){
    $("#hiddenOrganizationID").val(0);
    $("#hiddenOrganizationFlag").val("POST");
    $('.txt').val('');
  })

  $("#createOrgModal").on('shown.bs.modal', function(){
    $("#organization_name").focus();
  })

  $(document).on("click", "#btnorganizationsave", function(e){
    e.preventDefault();
    let flag = $("#hiddenOrganizationFlag").val();


    $.ajax({
        url: (flag == "POST" ? "{{ route('organizations.store') }}" : "{{ route('organizations.update') }}"),
        method: "POST",
        data: $("#frmOrganizationData").serialize(),
        beforeSend:function(){
            $("#organizationdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnorganizationsave").prop("disabled", true);
        },
        success: function (data) {
            $("#btnorganizationsave").prop("disabled", false);
            if(flag === "POST"){
              $("#organizationdatamsg").html("<div class = 'alert alert-success'>Organization data saved.</div>");
            }else {
              $("#organizationdatamsg").html("<div class = 'alert alert-success'>Organization data updated.</div>");
            }
            orglist();
            setTimeout(() => {
              if(flag === "POST"){
                $('.txt').val('');
              }
              $("#organization_name").focus();
            }, 1000);
        },

        error: function (response) {
            $("#btnorganizationsave").prop("disabled", false);
            var errors = response.responseJSON.errors;
            $("#organizationdatamsg").html(errors);
        }
    });
  });

  // view

  // edit
  $(document).on("click", '.btn-edit', function(e){
      let id = $(this).data('id');
      $.ajax({
          url: "{{ route('organizations.edit') }}",
          type: "POST",
          data: {id},
          beforeSend:function(){
              $("#createOrgModal").modal('toggle');
              $("#organizationdatamsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Populating, please wait...</div>");
          },
          success: function(data) {
            $("#organizationdatamsg").html("");
              // populate

            $("#hiddenOrganizationID").val(id);
            $("#hiddenOrganizationFlag").val("UPDATE");

          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#data").html(errors);
          }
      });
    });
  // delete

  // list

  function orglist(){
    $.ajax({
        url: "{{ route('organizations.list') }}",
        method: "POST",
        beforeSend:function(){
            $("#orglist ").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#orglist ").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#data").html(errors);
        }
    });
  }


// SEARCH ORGANIZATION----------

  $(document).on("keypress", "#searchOrg", function(e) {
      if (e.which === 13) { // 13 = Enter key
          e.preventDefault(); // Prevent form submission
          listorg();             // Call your AJAX list function
      }
  });

  // Trigger list() on button click
  $(document).on("click", "#btnSearchOrg", function(e) {
      e.preventDefault();
      listorg();                 // Call your AJAX list function
  });

  // account list ---------------
  function listorg(){
    let str = $("#searchOrg").val();
    $.ajax({
        url: "{{ route('organizations.list') }}",
        method: "POST",
        data: {str},
        beforeSend:function(){
            $("#orglist").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Generating, please wait...</div>");
        },
        success: function (data) {
            $("#orglist").html(data);
        },

        error: function (response) {
            var errors = response.responseJSON.errors;
            $("#data").html(errors);
        }
    });
  }






</script>
