<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use App\Models\UserLog;

class LogSuccessfulLogin
{
  public function handle(Login $event)
  {
    UserLog::create([
      'user_id' => $event->user->user_id ?? null,
      'action' => 'Logged in',
      'ip_address' => request()->ip(),
      'user_agent' => request()->header('User-Agent')
        ?? request()->server('HTTP_USER_AGENT')
        ?? 'Unknown',
    ]);
  }
}
