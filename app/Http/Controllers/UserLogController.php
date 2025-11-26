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
            $logs->where(function ($q2) use ($query) {
                $q2->whereHas('user', function ($q) use ($query) {
                    $q->where('username', 'LIKE', "%{$query}%");
                })
                ->orWhere('action', 'LIKE', "%{$query}%")
                ->orWhere('ip_address', 'LIKE', "%{$query}%")
                ->orWhere('user_agent', 'LIKE', "%{$query}%");
            });
        }

        // paginate instead of get()
        $logs = $logs->orderByDesc('created_at')->paginate(20);

        if ($request->ajax()) {
            return view('admin.users.logs-list', compact('logs'))->render();
        }

        return view('admin.users.logs', compact('logs'));
    }
}
