<?php

namespace App\Http\Controllers;

use App\Models\UserLog;
use Illuminate\Http\Request;

class UserLogController extends Controller
{
  public function index(Request $request)
  {
    $search = $request->input('search');

    $logs = UserLog::with('user')
      ->when($search, function ($query, $search) {
        $query->whereHas('user', function ($q) use ($search) {
          $q->where('username', 'like', "%{$search}%");
        })
          ->orWhere('action', 'like', "%{$search}%")
          ->orWhere('ip_address', 'like', "%{$search}%");
      })
      ->orderByDesc('created_at')
      ->get();
    // ->withQueryString();

    if ($request->ajax()) {
      return view('admin.partials.logs_table', compact('logs'))->render();
    }
    return view('admin.logs', compact('logs', 'search'));
  }
}
