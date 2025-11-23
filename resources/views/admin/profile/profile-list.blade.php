
<table class="table table-hover">
  <thead>
    <tr>
      <th>Last Name</th>
      <th>First Name</th>
      <th>Email</th>
      <th>Type</th>
      <th class="text-center">Actions</th>
    </tr>
  </thead>
  <tbody class="table-border-bottom-0">
    @forelse ($user_profiles as $user_profile)
      <tr>
        <td>{{ $user_profile->last_name }}</td>
        <td>{{ $user_profile->first_name }}</td>
        <td>{{ $user_profile->email }}</td>
        <td>{{ $user_profile->type }}</td>
        <td class="text-center">
          <button type="button" href="#" class="btn rounded-pill btn-icon btn-secondary btn-sm btn-view"
                data-id="{{ Crypt::encryptstring($user_profile->profile_id)}}">
            <i class="mdi mdi-account-eye"></i></a>
          </button>
          <button type="button" href="#" class="btn rounded-pill btn-icon btn-primary btn-sm btn-edit"
                data-id="{{ Crypt::encryptstring($user_profile->profile_id)}}">
                <i class="mdi mdi-text-box-edit-outline"></i>
          </button>

          <button type="button" class="btn rounded-pill btn-icon btn-danger btn-sm"
                  data-bs-target="#confirmDeleteModal" data-user-id=""
                  data-username="">
                  <i class=" mdi mdi-delete-forever"></i>
          </button>
        </td>
      </tr>

    @empty
      <tr>
        <td colspan="6" class="text-center text-muted">No Profiles found.</td>
      </tr>
    @endforelse
  </tbody>
</table>
@section('page-script')
@include('admin.profile.js')
@endsection
