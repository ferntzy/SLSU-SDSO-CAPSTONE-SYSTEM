<table class="table table-hover">
  <thead>
      <tr>
          <th>Organization Name</th>
          <th>Type</th>
          <th class = "text-center" >Members</th>
          <th>Adviser</th>
          <th>Officer</th>
          <th>Status</th>
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

              <td>
                  <span class="badge bg-label-{{ $org->status === 'Active' ? 'success' : 'secondary' }}">
                      {{ $org->status }}
                  </span>
              </td>

              <td class = "text-center">

                  <button type="button" href="#" class="btn rounded-pill btn-icon btn-secondary btn-sm btn-view"
                        data-id="">
                        <i class="mdi mdi-account-eye"></i></a>
                  </button>

                  <button type="button" href="#" class="btn rounded-pill btn-icon btn-primary btn-sm btn-edit"
                        data-id="{{ Crypt::encryptstring($org->organization_id)}}">
                        <i class="mdi mdi-text-box-edit-outline"></i>
                  </button>

                  <button type="button" href="#" class="btn rounded-pill btn-icon btn-danger btn-sm btn-delete"
                          data-url="">
                          <i class=" mdi mdi-delete-forever"></i>
                  </button>
              </td>
          </tr>
      @empty
          <tr>
              <td colspan="7" class="text-center text-muted">No organizations found.</td>
          </tr>
      @endforelse
  </tbody>
</table>


@section('page-script')
    @include("admin.organizations.orgjs")
@endsection
