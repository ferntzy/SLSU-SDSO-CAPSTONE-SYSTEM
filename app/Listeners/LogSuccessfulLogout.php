<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use App\Models\UserLog;

class LogSuccessfulLogout
{
  public function handle(Logout $event)
  {
    UserLog::create([
      'user_id' => $event->user->user_id ?? null,
      'action' => 'Logged out',
      'ip_address' => request()->ip(),
      'user_agent' => request()->header('User-Agent')
        ?? request()->server('HTTP_USER_AGENT')
        ?? 'Unknown',
    ]);
  }
}
