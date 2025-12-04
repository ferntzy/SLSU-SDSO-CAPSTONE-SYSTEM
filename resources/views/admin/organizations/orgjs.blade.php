<script>

  $.ajaxSetup({
      headers: {
          'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  //org members js
  let selectedStudents = [];

  $(document).on('change', '.select-student', function() {
      selectedStudents = [];
      $('.select-student:checked').each(function() {
          selectedStudents.push($(this).val());
      });
  });

  $(document).on("click", ".btn-add-members", function(e){
      e.preventDefault();
      var id = $(this).attr("data-id");
      $.ajax({
          url: "{{route('organizations.available-students')}}",
          method: "POST",
          data: {id},
          beforeSend: function() {
            $("#addMembersModal").modal("toggle");
          },
          success: function(response) {
            $("#addmembermsg").html(response);
          },
          error: function(xhr) {
              console.log(xhr.responseText);

              Swal.fire({
                  icon: 'error',
                  title: 'Error saving members'
              });

              $("#btnaddmembers").prop("disabled", false);
          }
      });
  })



  // SAVE BUTTON
  $('#btnaddmembers').on('click', function (e) {
      e.preventDefault();

      // Prevent AJAX if no students selected
      if(selectedStudents.length === 0){
          $("#addmembermsg").html("<div class='alert alert-warning'>Please select students</div>");
          return;
      }

      $.ajax({
          url: "{{route('organizations.add-members')}}",
          type: "POST",
          data: $("#frmMemberData").serialize(),
          beforeSend: function() {
              $("#addmembermsg").html("<div class='alert alert-warning'><i class='spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
              $("#btnaddmembers").prop("disabled", true);
          },
          success: function(response) {
              // // $("#addmembermsg").html("<div class = 'alert alert-success'>Members successfully added.</div>");
              // $("#btnaddmembers").prop("disabled", false);
              // // loadAvailableStudents();
              // $('.select-student').prop('checked', false);
              // selectedStudents = [];

              Swal.fire({
                  icon: 'success',
                  title: 'Success!',
                  html: response.success, // or response.success if you want exact message
                  timer: 2000,
                  timerProgressBar: true,
                  showConfirmButton: false
              });
              orglist();
              $("#addMembersModal").modal('hide');
          },
          error: function(xhr) {
              console.log(xhr.responseText);

              Swal.fire({
                  icon: 'error',
                  title: 'Error saving members'
              });

              $("#btnaddmembers").prop("disabled", false);
          }
      });
  });
  // function loadAvailableStudents() {
  //     var id = $(".btn-add-members").first().data("id");

  //     $.ajax({
  //         url: "{{route('organizations.available-students')}}",
  //         method: "POST",
  //         data: { id: id },
  //         success: function(response) {
  //             $("#studentlist").html(response);

  //             // Reset selections
  //             selectedStudents = [];
  //             $('.select-student').prop('checked', false);
  //         }
  //     });
  // }



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
            $('input[name="organization_name"]').val(data.organization_name);
            $('input[name="description"]').val(data.description);
            $('select[name="organization_type"]').val(data.organization_type);
            $('select[name="adviser_id"]').val(data.adviser_id);
            $('select[name="officer_id"]').val(data.officer_id);
            // console.log("Assigned officer_id:", data.officer_id);
            // console.log("All officer options:");
            // $('#officer_id option').each(function(){
            //     console.log($(this).val(), $(this).text());
            // });
            // console.log("FULL DATA:", data);
            // console.log("Officer officer_id:", data.officer_id);
            // console.log("Adviser adviser_id:", data.adviser_id);

            $("#hiddenOrganizationID").val(id);
            $("#hiddenOrganizationFlag").val("UPDATE");

          },
          error: function (response) {
              var errors = response.responseJSON.errors;
              $("#data").html(errors);
          }
      });
    });

  // add members




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
