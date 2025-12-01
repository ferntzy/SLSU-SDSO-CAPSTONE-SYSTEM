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
          selectedStudents.push($(this).val()); // get the value of checked checkbox
      });
  });

  $("#addMembersModal").on("shown.bs.modal", function(e){
      var encryptedId = $(e.relatedTarget).data('id'); // encrypted
      $("#hiddenOrgID").val(encryptedId);
  });


  // SAVE BUTTON
  $('#btnaddmembers').on('click', function (e) {
      e.preventDefault();
      let flag = $("hiddenOrgID").val();
      $.ajax({
          url: "{{route('organizations.add-members')}}",
          type: "POST",
          data: $("#frmMemberData").serialize(),
          beforeSend:function(){
            if(selectedStudents.length === 0){
              $("#addmembermsg").html("<div class = 'alert alert-warning'>please select students</div>");
              return;
            }
            $("#addmembermsg").html("<div class = 'alert alert-warning'><i class = 'spinner-grow spinner-grow-sm'></i> Saving, please wait...</div>");
            $("#btnaddmembers").prop("disabled", true);
          },
          success: function(response) {
            Swal.fire({
                icon: 'success',
                title: 'Members successfully added!',
                showConfirmButton: false,
                timer: 600
            });

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




// OPEN ADD OFFICER MODAL
$(document).on("click", ".btn-add-officers", function() {

    let encryptedId = $(this).data("id");

    $.ajax({
        url: "{{ route('organizations.edit') }}",
        type: "POST",
        data: { id: encryptedId },
        beforeSend: function() {
            $("#officerOrgName").text("Loading...");
            $("#officerAdviser").text("Adviser: Loading...");

            // Set temporary title
            $("#officerModalTitle").text("Add Officer in ...");
        },
        success: function(data) {

            // Fill modal fields
            $("#officerOrgName").text(data.organization_name);

            // Set modal title: Add Officer in {Org Name}
            $("#officerModalTitle").text(
                "Add Officer in " + data.organization_name
            );

            let adviser = data.adviser?.profile?.first_name
                        + " " +
                        data.adviser?.profile?.last_name;

            $("#officerAdviser").text("Adviser: " + (adviser || "N/A"));
        },
        error: function(xhr) {
            console.error(xhr.responseText);
            $("#officerOrgName").text("Error loading data");
            $("#officerAdviser").text("Adviser: Error");
            $("#officerModalTitle").text("Add Officer");
        }
    });

});




//add officer to hide if one profile_id exists on the other RoleNmae
  document.addEventListener("DOMContentLoaded", function () {
      const selects = document.querySelectorAll('.officer-select');

      function updateDropdowns() {
          // Get all selected profile_ids
          let selectedValues = Array.from(selects)
              .map(s => s.value)
              .filter(v => v !== ""); // remove empty

          selects.forEach(select => {
              let currentValue = select.value;

              // Loop through options
              Array.from(select.options).forEach(option => {
                  if (option.value === "") return; // skip "Select"

                  // Hide option if already chosen in another select
                  if (selectedValues.includes(option.value) && option.value !== currentValue) {
                      option.hidden = true;
                  } else {
                      option.hidden = false;
                  }
              });
          });
      }

      // Run filtering every time a select changes
      selects.forEach(select => {
          select.addEventListener("change", updateDropdowns);
      });

      // Initial run
      updateDropdowns();
  });


      // ===== Save Officers =====
    $(document).on("click", "#btnSaveOfficers", function(e) {
        e.preventDefault();

        let orgId = $("#officerOrgID").val();

        let officersData = {};
        $(".officer-select").each(function() {
            let role = $(this).data("role"); // original role name
            officersData[role] = $(this).val() || null;
        });

        $.ajax({
            url: "{{ route('organizations.save-officers') }}",
            method: "POST",
            data: {
                org_id: orgId,
                officers: officersData,
                _token: "{{ csrf_token() }}"
            },
            beforeSend: function() {
                $("#btnSaveOfficers").prop("disabled", true);
            },
            success: function(response) {
                Swal.fire({
                    icon: "success",
                    title: "Officers saved!",
                    timer: 1000,
                    showConfirmButton: false
                });
                $("#btnSaveOfficers").prop("disabled", false);
            },
            error: function(xhr) {
                console.error(xhr.responseText);
                Swal.fire({
                    icon: "error",
                    title: "Error saving officers"
                });
                $("#btnSaveOfficers").prop("disabled", false);
            }
        });
    });






</script>
