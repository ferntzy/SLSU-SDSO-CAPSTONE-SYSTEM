<div class="d-flex align-items-center mb-3">
    <img src="{{ asset($user->profile->profile_picture_path ?? 'assets/images/slsu_logo.png') }}"
         class="rounded-circle me-3" width="80" height="80">
    <div>
        <h4>{{ $user->profile->first_name }} {{ $user->profile->last_name }}</h4>
        <p class="text-muted">{{ $user->account_role }}</p>
           <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ $user->username }}" required>
          </div>
    </div>
</div>

<hr>

<p><strong>Contact #:</strong> {{ $user->profile->contact_number }}</p>
<p><strong>Address:</strong> {{ $user->profile->address }}</p>
<p><strong>Office:</strong> {{ $user->profile->office }}</p>
<p><strong>Joined:</strong> {{ $user->created_at->format('Y-m-d') }}</p>
<p><strong>Status:</strong> {{ $user->status }}</p>
