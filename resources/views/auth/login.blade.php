
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>

  <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
  {{-- <link rel="stylesheet" href="{{ asset('assets/vendor/css/theme-default.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/demo.css') }}"> --}}
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">


  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">


</head>

<body>

<div class="login-container">
  <div class="login-card">

    <!-- LEFT SIDE: LOGIN FORM -->
    <div class="login-left">
    <h4 class="login-title">{{config('variables.templateSuffix')}}</h4>
      <h6 class="text-center">{{config('variables.templateName')}}</h6>

      <div class="card shadow p-4 login-box">
        {{-- <h4 class="text-center mb-4">WELCOME</h4> --}}

        @if ($errors->any())
          <div class="alert alert-danger">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
          @csrf

          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
          </div>

          <div class="mb-3">
            <label for="password" class="form-label">Password</label>
            <input type="password" name="password" class="form-control" required>
          </div>
          <div class="checkbox-form-check mt-2">
            <input type="checkbox" class="checkbox-form-check-input" id="showPasswordCheck">
            <label class="checkbox-form-check-label" for="showPasswordCheck">Show Password</label>
          </div>


          <button type="submit" class="btn btn-primary w-100 login-btn">Login</button>
        </form>
      </div>
    </div>

    <!-- RIGHT SIDE: ILLUSTRATION -->
    <div class="login-right">
      <img src="{{ asset('images/click.png') }}" alt="SIS Illustration" class="login-image">
    </div>

  </div>
</div>

  {{-- show password --}}
  <script>
    const passwordInput = document.querySelector('input[name="password"]');
    const showPasswordCheck = document.getElementById('showPasswordCheck');

    showPasswordCheck.addEventListener('change', function() {
      if(this.checked) {
        passwordInput.type = 'text';
      } else {
        passwordInput.type = 'password';
      }
    });
  </script>


  <!-- jQuery + Bootstrap -->
  <script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
  <script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

  <!-- SweetAlert2 JS -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  @if (session('logout_success'))
    <script>
      document.addEventListener("DOMContentLoaded", function () {
        Swal.fire({
          icon: 'success',
          title: 'Logged Out',
          text: '{{ session('logout_success') }}',
          showConfirmButton: false,
          timer: 2500,
          timerProgressBar: true
        });
      });
    </script>
  @endif

</body>

</html>
