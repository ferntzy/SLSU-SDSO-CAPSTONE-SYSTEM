<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password</title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="{{ asset('assets/css/login-forgotpass.css') }}">
</head>
<body>

<div class="forgot-container">

  <!-- Message Card -->
  <div class="forgot-card">
    <h1 class="logo-title">{{ config('variables.templateSuffix') }}</h1>
    <p class="subtitle">{{ config('variables.templateName') }}</p>

    <div class="icon-circle">
      <i class="bi bi-building"></i>
    </div>

    <h2 class="info-title">Password Reset Assistance</h2>
    <p class="info-text">
      <strong class="highlight">Please visit the Student Development Office</strong><br>
      for assistance of forgotten password.
    </p>
    <p class="text-muted" style="font-size: 0.95rem;">
      Our staff will be happy to help you regain access to your account in person.
    </p>

    <a href="{{ route('login') }}" class="back-btn">
      <i class="bi bi-arrow-left"></i> Back to Login
    </a>
  </div>

  <!-- Illustration -->
  <div class="illustration-wrapper">
    <img src="{{ asset('assets/img/illustrations/forgotpassword.png') }}"
         alt="Forgot Password Illustration"
         class="illustration-img">
  </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>
</html>
