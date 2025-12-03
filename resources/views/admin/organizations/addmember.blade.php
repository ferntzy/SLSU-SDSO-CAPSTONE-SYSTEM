<form id="frmMemberData">
  <input hidden type="text" id = "hiddenOrgID" name="organization_id" value = "{{Crypt::encryptstring($orgId)}}">

  <div class="col-md-12 col-sm-12">
    <div id="vertical-example"
        class="overflow-auto border rounded p-3"
        style="height: 500px;">

        <table class="table table-hover">
          <thead>
            <tr>
              <th>List of students</th>
              <th class="text-end">Select</th>
            </tr>
          </thead>
          <tbody id="studentlist">
            @forelse($students as $student)
            <tr data-profile-id="{{ $student->profile_id }}">
              <td>{{$student->first_name}}</td>
              <td class ="text-end ">
                  <input style="margin-right: 15px;"
                    class="form-check-input select-student"
                    type="checkbox"
                    name="students[]"
                    value="{{ $student->profile_id }}">
              </td>
            </tr>
            @empty
                <tr>
                    <td colspan="2" class="text-center">No students found</td>
                </tr>
            @endforelse
          </tbody>
        </table>
    </div>
  </div>
</form>
