
<table class="table table-hover">
  <thead>
    <tr>
      <th>Username</th>
      <th>Last Name</th>
      <th>First Name</th>
      <th>Role</th>
      <th>Type</th>
      <th>Time</th>
      <th>Date Created</th>

      <th class="text-center">Actions</th>
    </tr>
  </thead>
  <tbody class="table-border-bottom-0">
    @forelse ($user_accounts as $user_account)
      <tr>
        <td>{{ $user_account->username  ?? ''}}</td>
        <td>{{ $user_account->last_name ?? '' }}</td>
        <td>{{ $user_account->profile->first_name  ?? '' }}</td>
        <td><span class="badge bg-label-info">{{ $user_account->account_role }}</span></td>
        <td>{{ $user_account->profile->type  ?? ''}}</td>
        <td>{{ $user_account->created_at->format('Y-m-d') }}</td>
        <td>{{ $user_account->created_at->format('H:i:s') }}</td>
        <td class="text-center">
          <a href="" class="btn rounded-pill btn-icon btn-secondary btn-sm"><i class="mdi mdi-account-eye"></i></a>

        <button type="button"
                class="btn rounded-pill btn-icon btn-primary btn-sm btn-edit"
                data-bs-target="#editAccountModal"
                data-id="{{ Crypt::encryptString($user_account->user_id) }}">
            <i class="mdi mdi-text-box-edit-outline"></i>
        </button>


          <button type="button" class="btn rounded-pill btn-icon btn-danger btn-sm"
                  data-bs-target="#confirmDeleteModal"
                   data-id="{{ Crypt::encryptstring($user_account->user_id)}}">
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

