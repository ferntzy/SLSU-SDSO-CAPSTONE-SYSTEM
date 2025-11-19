


<!-- jQuery first (if your scripts need it) -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>

<!-- Bootstrap Bundle (includes Popper.js) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert2 -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
  document.addEventListener('DOMContentLoaded', function() {

  /* ==========================
     BOOTSTRAP SAFETY CHECK
  ========================== */
  if (typeof bootstrap === 'undefined') {
    console.error('Bootstrap JS not loaded!');
  }

  /* ==========================
     TOGGLE PASSWORDS (ANY MODAL OR FORM)
  ========================== */
  document.addEventListener('click', function(e) {
    // Edit / Create / Admin password toggle
    if (e.target.closest('.toggle-password')) {
      const toggle = e.target.closest('.toggle-password');
      const input = toggle.closest('.input-group').querySelector('input');
      const icon = toggle.querySelector('i');
      if (!input || !icon) return;

      if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('bi-eye-slash','bi-eye');
      } else {
        input.type = 'password';
        icon.classList.replace('bi-eye','bi-eye-slash');
      }
    }
  });

  // /* ==========================
  //    EDIT USER MODAL
  // ========================== */
  const editUserModalEl = document.getElementById('editUserModal');
  const editUserForm = document.getElementById('editUserForm');
  if(editUserModalEl && editUserForm){
    document.querySelectorAll('.editUserBtn').forEach(button => {
      button.addEventListener('click', function() {
        // Fill modal fields
        const userId = this.dataset.userId;
        document.getElementById('edit-user-id').value = userId || '';
        document.getElementById('edit-username').value = this.dataset.username || '';
        document.getElementById('edit-email').value = this.dataset.email || '';
        document.getElementById('edit-role').value = this.dataset.role || '';
        document.getElementById('edit-password').value = '';

        // Set form action dynamically
      //  editUserForm.action = `{{ url('/users') }}/${userId}`;
       editUserForm.action = `{{ route('users.update', ':id') }}`.replace(':id', userId);



        // Show modal
        new bootstrap.Modal(editUserModalEl).show();
      });
    });
  }

  /* ==========================
     DELETE USER MODAL
  ========================== */
  const deleteModalEl = document.getElementById('confirmDeleteModal');
  const deleteForm = document.getElementById('deleteUserForm');
  if(deleteModalEl && deleteForm){
    deleteModalEl.addEventListener('show.bs.modal', function(event){
      const button = event.relatedTarget;
      if(!button) return;

      const userId = button.dataset.userId;
      const username = button.dataset.username || 'this account';

      deleteForm.action = "{{ route('users.destroy', ':id') }}".replace(':id', userId);
      const messageEl = document.getElementById('deleteMessage');
      if(messageEl) messageEl.textContent = `Are you sure you want to delete "${username}"?`;

      // Reset password and error
      const adminPasswordInput = document.getElementById('adminPassword');
      const passwordError = document.getElementById('passwordError');
      if(adminPasswordInput) adminPasswordInput.value = '';
      if(passwordError) passwordError.classList.add('d-none');
    });
  }

  /* ==========================
     AVATAR SIZE VALIDATION (10MB max)
  ========================== */
  const avatarInput = document.getElementById('avatar'); // Adjust if needed

  if (avatarInput) {
    avatarInput.addEventListener('change', function () {
      const file = this.files[0];
      if (file) {
        const fileSizeKB = file.size / 1024;
        if (fileSizeKB > 10240) { // 10MB = 10240KB
          Swal.fire({
            icon: 'warning',
            title: 'File Too Large!',
            text: 'The selected image exceeds 10MB. Please upload a smaller file.',
            confirmButtonColor: '#d33',
          });
          this.value = ''; // Reset file input
        }
      }
    });
  }

  /* ==========================
     CREATE ACCOUNT PAGE PASSWORD CHECK
     (only if elements exist)
  ========================== */
  const usernameInput = document.getElementById('username');
  const emailInput = document.getElementById('email');
  const passwordInput = document.querySelector('input[name="password"]');
  const passwordConfirmInput = document.querySelector('input[name="password_confirmation"]');
  const form = document.querySelector('form');
  const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

  if(usernameInput && emailInput && passwordInput && passwordConfirmInput && form){
    let usernameTaken = false;
    let emailTaken = false;
    let timer;

    function debounceCheck(field, value){
      clearTimeout(timer);
      timer = setTimeout(()=>checkAvailability(field,value),400);
    }

    async function checkAvailability(field,value){
      if(!value.trim()) return;
      try {
        const res = await fetch("{{ route('users.checkAvailability') }}",{
          method:'POST',
          headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN': csrfToken },
          body: JSON.stringify({field,value})
        });
        const data = await res.json();
        if(field==='username'){
          usernameTaken = !data.available;
          document.getElementById('username-error').style.display = usernameTaken ? 'block' : 'none';
          usernameInput.classList.toggle('is-invalid', usernameTaken);
        } else if(field==='email'){
          emailTaken = !data.available;
          document.getElementById('email-error').style.display = emailTaken ? 'block' : 'none';
          emailInput.classList.toggle('is-invalid', emailTaken);
        }
      } catch(err){ console.error(err); }
    }

    usernameInput.addEventListener('input', e=>debounceCheck('username', e.target.value));
    emailInput.addEventListener('input', e=>debounceCheck('email', e.target.value));

    function checkPasswords(){
      if(passwordInput.value && passwordConfirmInput.value && passwordInput.value !== passwordConfirmInput.value){
        document.getElementById('password-match-error').style.display='block';
        passwordConfirmInput.classList.add('is-invalid');
        return false;
      } else {
        document.getElementById('password-match-error').style.display='none';
        passwordConfirmInput.classList.remove('is-invalid');
        return true;
      }
    }

    passwordInput.addEventListener('input', checkPasswords);
    passwordConfirmInput.addEventListener('input', checkPasswords);

    form.addEventListener('submit', function(e){
      e.preventDefault();
      const passwordsMatch = checkPasswords();
      const passwordTooShort = passwordInput.value.length < 6;

      if(usernameTaken || emailTaken || !passwordsMatch || passwordTooShort){
        let msg='';
        if(usernameTaken && emailTaken) msg='Both username and email are already used!';
        else if(usernameTaken) msg='Username is already used!';
        else if(emailTaken) msg='Email is already used!';
        else if(passwordTooShort) msg='Password must be at least 6 characters!';
        else if(!passwordsMatch) msg='Passwords do not match!';

        Swal.fire({icon:'error', title:'Validation Error', text:msg, confirmButtonColor:'#d33'});
        return false;
      }

      // Show loading
      Swal.fire({title:'Creating Account...', text:'Please wait.', icon:'info', showConfirmButton:false, allowOutsideClick:false, didOpen:()=>Swal.showLoading()});

      // Submit via fetch
      const formData = new FormData(form);
      fetch(form.action, {method: form.method||'POST', headers:{'X-CSRF-TOKEN': csrfToken}, body: formData})
      .then(async res=>{
        const data = await res.json().catch(()=>({}));
        Swal.close();
        if(res.ok){
          Swal.fire({icon:'success', title:'Account Created!', text:data.message||'Success!', confirmButtonColor:'#3085d6'}).then(()=>window.location.href='{{ route("users.index") }}');
        } else {
          Swal.fire({icon:'error', title:'Error', text:data.message||'Please try again.', confirmButtonColor:'#d33'});
        }
      }).catch(err=>{ Swal.close(); console.error(err); });
    });
  }

}); // DOMContentLoaded end
</script>


