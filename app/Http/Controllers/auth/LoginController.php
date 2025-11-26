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

    public function showForgotPasswordForm()
    {
        return view('auth.reset_pass'); // points to your Blade file
    }

  public function sendResetLink(Request $request)
{
    $request->validate([
        'username' => 'required|string',
        'email' => 'required|email',
    ]);

    $user = User::where('username', $request->username)
                ->where('email', $request->email)
                ->first();

    if (!$user) {
        return back()->withErrors(['username' => 'User not found with that email.']);
    }

    // Send reset token via notification
    $token = Str::random(60);
    DB::table('password_resets')->insert([
        'username' => $user->username,
        'token' => Hash::make($token),
        'created_at' => now(),
    ]);

    $user->notify(new \App\Notifications\ResetPasswordNotification($token));

    return back()->with('status', 'Password reset link has been sent to your email.');
}

 // Show the reset password form
    public function showResetPasswordForm($token, Request $request)
    {
        $username = $request->query('username');
        return view('auth.reset_password', compact('token', 'username'));
    }

    // Handle new password submission
    public function resetPassword(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'token' => 'required|string',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Find the token in password_resets table
        $reset = DB::table('password_resets')
                    ->where('username', $request->username)
                    ->first();

        if (!$reset) {
            return back()->withErrors(['username' => 'Invalid or expired reset token.']);
        }

        // Verify token
        if (!Hash::check($request->token, $reset->token)) {
            return back()->withErrors(['token' => 'Invalid or expired reset token.']);
        }

        // Update user password
        $user = User::where('username', $request->username)->first();
        $user->password = Hash::make($request->password);
        $user->save();

        // Delete used token
        DB::table('password_resets')->where('username', $request->username)->delete();

        return redirect()->route('login')->with('status', 'Your password has been reset successfully.');
    }



}
class User extends Authenticatable
{
  protected $primaryKey = 'user_id'; // Important for your custom column name
  public $incrementing = true;
  protected $fillable = ['username', 'password', 'account_role'];
}
