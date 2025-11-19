<!DOCTYPE html>

<html class="light-style layout-menu-fixed" data-theme="theme-default" data-assets-path="{{ asset('/assets') . '/' }}"
  data-base-url="{{url('/')}}" data-framework="laravel" data-template="vertical-menu-laravel-template-free">

<head>
  <meta charset="utf-8" />
  <meta name="viewport"
    content="width=device-width, initial-scale=1.0, user-scalable=no, minimum-scale=1.0, maximum-scale=1.0" />

  <title>@yield('title') | Materio - HTML Laravel Free Admin Template </title>
  <meta name="description"
    content="{{ config('variables.templateDescription') ? config('variables.templateDescription') : '' }}" />
  <meta name="keywords" content="{{ config('variables.templateKeyword') ? config('variables.templateKeyword') : '' }}">
  <!-- laravel CRUD token -->
  <meta name="csrf-token" content="{{ csrf_token() }}">
  <!-- Canonical SEO -->
  <link rel="canonical" href="{{ config('variables.productPage') ? config('variables.productPage') : '' }}">
  <!-- Favicon -->
  <link rel="icon" type="image/x-icon" href="{{ asset('assets/img/favicon/favicon.ico') }}" />



  <!-- Include Styles -->
  @include('layouts/sections/styles')

  <!-- Include Scripts for customizer, helper, analytics, config -->
  @include('layouts/sections/scriptsIncludes')

</head>

<body>


  <!-- Layout Content -->
  @yield('layoutContent')
  <!--/ Layout Content -->



  <!-- Include Scripts -->
  @include('layouts/sections/scripts')
  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const contentContainer = document.querySelector("#main-content");
      const menuLinks = document.querySelectorAll(".menu-link");

      if (!contentContainer) return;

      menuLinks.forEach(link => {
        link.addEventListener("click", e => {
          const href = link.getAttribute("href");
          if (!href || href === "#" || href.startsWith("javascript")) return;

          // Skip AJAX for login, logout, or external routes
          if (href.includes("/login") || href.includes("/logout") || href.includes("http")) return;

          e.preventDefault();

          // Loading animation
          contentContainer.innerHTML = `
        <div class="text-center p-5">
          <div class="spinner-border text-primary" role="status"></div>
          <p class="mt-3">Loading page...</p>
        </div>
      `;

          fetch(href, { headers: { "X-Requested-With": "XMLHttpRequest" } })
            .then(response => {
              if (!response.ok) throw new Error("Network error");
              return response.text();
            })
            .then(html => {
              const parser = new DOMParser();
              const doc = parser.parseFromString(html, "text/html");
              const newContent = doc.querySelector("#main-content");

              if (!newContent) {
                window.location.href = href; // fallback if page has no container
                return;
              }

              // Replace content
              contentContainer.innerHTML = newContent.innerHTML;

              // Update active link
              menuLinks.forEach(l => l.classList.remove("active"));
              link.classList.add("active");

              // Update URL (no reload)
              window.history.pushState({}, "", href);

              // Reinitialize Materio helpers (menus, tooltips, etc.)
              if (typeof window.Helpers !== "undefined") {
                window.Helpers.initCustomOptionCheck();
              }
            })
            .catch(err => {
              console.error("Error loading page:", err);
              window.location.href = href; // fallback on error
            });
        });
      });
    });
  </script>

</body>

</html>