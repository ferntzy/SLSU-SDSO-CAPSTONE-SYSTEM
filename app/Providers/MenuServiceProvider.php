<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;

class MenuServiceProvider extends ServiceProvider
{
  /**
   * Register services.
   */
  public function register(): void
  {
    //
  }

  /**
   * Bootstrap services.
   */
  public function boot(): void
  {
    // Default menu
    $menuFile = base_path('resources/menu/verticalMenu.json');

    // Use a view composer to ensure Auth::user() is available
    View::composer('*', function ($view) use ($menuFile) {
      $menuPath = $menuFile;

      if (Auth::check()) {
        $role = Auth::user()->account_role ?? 'default';

        $menuMap = [
          'admin' => base_path('resources/menu/admin-menu.json'),
          'Student_Organization' => base_path('resources/menu/student-menu.json'),
          'Faculty_Adviser' => base_path('resources/menu/faculty-menu.json'),
          'SDSO_Head' => base_path('resources/menu/sdso-menu.json'),
          'VP_SAS' => base_path('resources/menu/vpsas-menu.json'),
          'SAS_Director' => base_path('resources/menu/sas-menu.json'),
          'BARGO' => base_path('resources/menu/bargo-menu.json'),
        ];

        if (isset($menuMap[$role]) && file_exists($menuMap[$role])) {
          $menuPath = $menuMap[$role];
        }
      }

      // Load and share menu
      $menuJson = file_get_contents($menuPath);
      $menuData = json_decode($menuJson);

      $view->with('menuData', [$menuData]);
    });
  }
}
