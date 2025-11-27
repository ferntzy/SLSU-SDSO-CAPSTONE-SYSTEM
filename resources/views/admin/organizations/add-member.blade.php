<div class="modal fade" id="addMembersModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">
    <div class="modal-dialog modal-m modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header text-white">
                <h5 class="modal-title ">ADD MEMBERS</h5>
                <button type="button" class="btn-close position-absolute end-0 me-2" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <!-- BODY -->
            <div class="modal-body">
              <div id="addmembermsg"></div>
              <form id="frmMemberData">
                @csrf
                <input type="hidden" id = "hiddenOrgID" name="organization_id">

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
                        <tbody>
                          @forelse($students as $student)
                          <tr>
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
            </div>

            <!-- FOOTER -->
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="btnaddmembers">Save</button>
            </div>
        </div>
    </div>
</div>


