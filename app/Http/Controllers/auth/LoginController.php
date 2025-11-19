<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\User as Authenticatable;

class LoginController extends Controller
{
  public function showLoginForm()
  {
    return view('auth.login');
  }

  public function login(Request $request)
  {
    $credentials = $request->validate([
      'username' => ['required'],
      'password' => ['required'],
    ]);

    if (Auth::attempt($credentials)) {
      $request->session()->regenerate();

      $user = Auth::user();

      // Redirect based on account_role
      switch ($user->account_role) {
        case 'admin':
          return redirect()->intended('/admin/dashboard');
        case 'Student_Organization':
          return redirect()->intended('/student/dashboard');
        case 'Faculty_Adviser':
          return redirect()->intended('/adviser/dashboard');
        case 'SDSO_Head':
          return redirect()->intended('/sdso/dashboard');
        case 'VP_SAS':
          return redirect()->intended('/vpsas/dashboard');
        case 'SAS_Director':
          return redirect()->intended('/sas/dashboard');
        case 'BARGO':
          return redirect()->intended('/bargo/dashboard');
        default:
          return redirect()->intended('/dashboard');
      }
    }

    return back()->withErrors([
      'username' => 'Invalid credentials.',
    ]);
  }

  public function logout(Request $request)
  {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    $request->session()->flush();
    return redirect('/login')->with('logout_success', 'You have been logged out successfully!');
  }
}
class User extends Authenticatable
{
  protected $primaryKey = 'user_id'; // Important for your custom column name
  public $incrementing = true;
  protected $fillable = ['username', 'password', 'account_role'];
}
