<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <link rel="stylesheet" href="{{ asset('assets/vendor/css/core.css') }}">
  <link rel="stylesheet" href="{{ asset('assets/css/login.css') }}">

  <!-- SweetAlert2 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>

<body>

<div class="login-container">
  <div class="login-card">

    <!-- LEFT SIDE: FORGOT PASSWORD FORM -->
    <div class="login-left">
      <h4 class="login-title">{{ config('variables.templateSuffix') }}</h4>
      <h6 class="text-center">{{ config('variables.templateName') }}</h6>

      <div class="card shadow p-4 login-box">
        <h5 class="text-center mb-3">Forgot Password</h5>
        <p class="text-muted text-center small mb-4">
          Enter your username and email to receive a password reset link.
        </p>

        @if (session('status'))
          <div class="alert alert-success">
            {{ session('status') }}
          </div>
        @endif

        @if ($errors->any())
          <div class="alert alert-danger">
            {{ $errors->first() }}
          </div>
        @endif

        <form method="POST" action="{{ route('password.forgot') }}">
          @csrf

          <div class="mb-3">
            <label for="username" class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="{{ old('username') }}" required autofocus>
          </div>

          <div class="mb-3">
            <label for="email" class="form-label">Email</label>
            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>
          </div>

          <button type="submit" class="btn btn-primary w-100">Send Reset Link</button>

          <div class="text-center mt-3">
            <a href="{{ route('login') }}" class="small text-decoration-none">Back to Login</a>
          </div>
        </form>
      </div>
    </div>

    <!-- RIGHT SIDE: ILLUSTRATION -->
    <div class="login-right">
      <img src="{{ asset('images/click.png') }}" alt="SIS Illustration" class="login-image">
    </div>

  </div>
</div>

<!-- jQuery + Bootstrap -->
<script src="{{ asset('assets/vendor/libs/jquery/jquery.js') }}"></script>
<script src="{{ asset('assets/vendor/js/bootstrap.js') }}"></script>

<!-- SweetAlert2 JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>
