@php
  $containerNav = $containerNav ?? 'container-fluid';
  $navbarDetached = ($navbarDetached ?? '');

@endphp
<!-- Navbar -->
<nav class="layout-navbar navbar navbar-expand-xl align-items-center bg-navbar-theme" id="layout-navbar">
  <div class="container-fluid">
    <!-- Brand demo -->
    @if(isset($navbarFull))
      <div class="navbar-brand app-brand demo d-none d-xl-flex py-0 me-4">
        <a href="{{ url('/') }}" class="app-brand-link gap-2">
          <span class="app-brand-logo demo">
            @include('_partials.macros', ["height" => 20])
          </span>
          <span class="app-brand-text demo menu-text fw-semibold ms-1">{{ config('variables.templateName') }}</span>
        </a>
        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto d-block d-xl-none">
          <i class="mdi menu-toggle-icon d-xl-block align-middle mdi-20px"></i>
        </a>
      </div>
    @endif

    <!-- Menu toggle -->
    @if(!isset($navbarHideToggle))
      <div
        class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 {{ isset($menuHorizontal) ? ' d-xl-none ' : '' }} {{ isset($contentNavbar) ? ' d-xl-none ' : '' }}">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
          <i class="mdi mdi-menu mdi-24px"></i>
        </a>
      </div>
    @endif

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
      <!-- Search -->
      @php
        $searchVisibleRoutes = ['admin.logs', 'users.index'];
      @endphp

      {{-- @if(in_array(Route::currentRouteName(), $searchVisibleRoutes))
        <div class="navbar-nav align-items-center">
          <div class="nav-item d-flex align-items-center">
            <i class="mdi mdi-magnify mdi-24px lh-0"></i>
            <input type="text" id="globalSearch" class="form-control border-0 shadow-none bg-body" placeholder="Search..."
              aria-label="Search...">
          </div>
        </div>
      @endif --}}




      <!-- /Search -->
      <ul class="navbar-nav flex-row align-items-center ms-auto">
        <!--  Notifications Dropdown -->
        <li class="nav-item dropdown me-3 position-relative">
          <a class="nav-link dropdown-toggle hide-arrow position-relative" href="javascript:void(0);"
            id="notificationsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="mdi mdi-bell-outline mdi-24px"></i>

            <!-- 🔔 Notification Badge -->
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
              3
              <span class="visually-hidden">unread messages</span>
            </span>
          </a>

          <ul class="dropdown-menu dropdown-menu-end mt-3 py-2" aria-labelledby="notificationsDropdown"
            style="min-width: 300px;">
            <li class="dropdown-header fw-semibold text-uppercase small text-muted px-3">
              Notifications
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-start" href="javascript:void(0);">
                <div class="flex-shrink-0 me-2">
                  <i class="mdi mdi-calendar-plus-outline text-primary mdi-20px"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">New event added</h6>
                  <small class="text-muted">2 mins ago</small>
                </div>
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-start" href="javascript:void(0);">
                <div class="flex-shrink-0 me-2">
                  <i class="mdi mdi-account-plus-outline text-success mdi-20px"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">New user registered</h6>
                  <small class="text-muted">15 mins ago</small>
                </div>
              </a>
            </li>
            <li>
              <a class="dropdown-item d-flex align-items-start" href="javascript:void(0);">
                <div class="flex-shrink-0 me-2">
                  <i class="mdi mdi-alert-circle-outline text-warning mdi-20px"></i>
                </div>
                <div class="flex-grow-1">
                  <h6 class="mb-0">System update available</h6>
                  <small class="text-muted">1 hour ago</small>
                </div>
              </a>
            </li>
            <li>
              <hr class="dropdown-divider">
            </li>
            <li>
              <a class="dropdown-item text-center text-primary fw-semibold" href="javascript:void(0);">
                View all notifications
              </a>
            </li>
          </ul>
        </li>
      </ul>







      <!-- User dropdown -->
      {{-- <li class="nav-item navbar-dropdown dropdown-user dropdown">
        <a class="nav-link dropdown-toggle hide-arrow p-0" href="javascript:void(0);" data-bs-toggle="dropdown">
          <div class="avatar avatar-online">
            <img src="{{ asset('images/slsu_logo.png') }}" alt class="w-px-40 h-auto rounded-circle">
          </div>
        </a>
        <ul class="dropdown-menu dropdown-menu-end mt-3 py-2">
          <li>
            <a class="dropdown-item pb-2 mb-1" href="javascript:void(0);">
              <div>
                <span class="fw-medium d-block" style="text-align: center; text-transform: uppercase;">
                  {{ Auth::user()?->account_role }}
                </span>
              </div>
            </a>
          </li>
          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
            <a class="dropdown-item" href="">
              <i class="mdi mdi-account-outline me-1 mdi-20px"></i>
              <span class="align-middle">My Profile</span>
            </a>

          </li>
          <li>
            <a class="dropdown-item" href="javascript:void(0);">
              <i class="mdi mdi-cog-outline me-1 mdi-20px"></i>
              <span class="align-middle">Settings</span>
            </a>
          </li>

          <li>
            <div class="dropdown-divider my-1"></div>
          </li>
          <li>
           <a class="dropdown-item" href="{{ route('logout') }}">
              <i class="mdi mdi-power me-1 mdi-20px"></i>
              <span class="align-middle">Log Out</span>
            </a>

          </li>
        </ul>
      </li> --}}
      </ul>
    </div>
  </div>
</nav>
<!-- / Navbar -->
