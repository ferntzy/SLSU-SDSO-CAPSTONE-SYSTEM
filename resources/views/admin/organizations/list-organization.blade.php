<table class="table table-hover">
  <thead>
      <tr>
          <th>Organization Name</th>
          <th>Type</th>
          <th class = "text-center" >Members</th>
          <th>Adviser</th>
          <th>President</th>
          <th class = "text-center">Actions</th>
      </tr>
  </thead>

  <tbody>
      @forelse($organizations as $org)
          <tr id="org-{{ $org->organization_id }}">
              <td>{{ $org->organization_name }}</td>
              <td>{{ $org->organization_type }}</td>
              <td class = "text-center" >{{ $org->members_count }}</td>

              <td>
                  {{ optional($org->adviser)->profile->first_name ?? '' }}
                  {{ optional($org->adviser)->profile->last_name ?? '' }}
              </td>

              <td>
                  @if($org->officers->count())
                      {{ $org->officers->first()->profile->first_name ?? '' }}
                      {{ $org->officers->first()->profile->last_name ?? '' }}
                  @else
                      N/A
                  @endif
              </td>

              <td class = "text-center">
                <div class="dropdown">
                  <a  href="#" class="text-primary btn-add-members"
                        data-bs-toggle="modal"
                        data-bs-target="#addMembersModal" data-id="{{Crypt::encryptstring($org->organization_id)}}" >
                        <i class="mdi mdi-account-multiple-plus-outline"></i>
                  </a>
                  <a href="#" class=" text-secondary btn-add-officers"
                      data-bs-toggle="modal"
                      data-bs-target="#addOfficersModal" data-id="{{ Crypt::encryptstring($org->organization_id) }}">
                      <i class="mdi mdi-account-plus-outline"></i>
                  </a>

                  <button type="button" class="btn p-0 dropdown-toggle hide-arrow" data-bs-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></button>
                  <div class="dropdown-menu">
                      <a class="dropdown-item text-warning btn-edit" href="javascript:void(0);" data-id="{{ Crypt::encryptstring($org->organization_id)}}"><i class="mdi  mdi-text-box-edit-outline me-1"></i>Edit</a>
                      <a class="text-primary dropdown-item" href="#" ><span class="mdi mdi-eye-settings-outline me-1"></span>View</a>
                      <hr>
                      <a  class="dropdown-item text-danger" href="javascript:void(0);"><i class=" mdi mdi-trash-can-outline me-1"></i>Delete</a>
                  </div>
                </div>
              </td>
          </tr>
      @empty
          <tr>
              <td colspan="7" class="text-center text-muted">No organizations found.</td>
          </tr>
      @endforelse
  </tbody>
</table>



