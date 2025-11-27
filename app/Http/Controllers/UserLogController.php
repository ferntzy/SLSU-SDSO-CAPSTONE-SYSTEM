<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->str;

        // Load logs + user if user still exists
        $logs = UserLog::with('user');

        if (!empty($query)) {
            $logs->where(function ($q) use ($query) {

                // Search in logs.username (PERMANENT)
                $q->where('username', 'LIKE', "%{$query}%")

                // Search in action, IP, user agent
                ->orWhere('action', 'LIKE', "%{$query}%")
                ->orWhere('ip_address', 'LIKE', "%{$query}%")
                ->orWhere('user_agent', 'LIKE', "%{$query}%")

                // Also search in user table but optional
                ->orWhereHas('user', function ($q2) use ($query) {
                    $q2->where('username', 'LIKE', "%{$query}%");
                });
            });
        }

        $logs = $logs->orderByDesc('created_at')->paginate(25);

        if ($request->ajax()) {
            return view('admin.users.logs-list', compact('logs'))->render();
        }

        return view('admin.users.logs', compact('logs'));
    }


        // LogController.php
   public function bulkDelete(Request $request)
{
    $ids = $request->ids;

    if (!$ids || !is_array($ids)) {
        return response()->json(['success' => false, 'message' => 'No IDs provided']);
    }

    UserLog::whereIn('id', $ids)->delete();

    return response()->json(['success' => true]);
}





}

