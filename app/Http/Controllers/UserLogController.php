<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
  public function index(Request $request)
{
    $query = $request->str;
    $logs = UserLog::with('user');

    if (!empty($query)) {
        $logs = $logs->whereHas('user', function ($q) use ($query) {
            $q->where('username', 'LIKE', "%{$query}%");
        })
        ->orWhere('action', 'LIKE', "%{$query}%")
        ->orWhere('ip_address', 'LIKE', "%{$query}%")
        ->orWhere('user_agent', 'LIKE', "%{$query}%");
    }

    $logs = $logs->orderByDesc('created_at')->get();

    if ($request->ajax()) {
        return view('admin.users.logs-list', compact('logs', 'query'))->render();
    }

    return view('admin.users.logs', compact('logs', 'query'));
}




}
