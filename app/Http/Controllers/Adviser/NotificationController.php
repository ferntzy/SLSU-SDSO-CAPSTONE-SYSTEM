<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:Faculty_Adviser');
    }

    // This returns unread notifications for the bell dropdown
    public function unread()
    {
        $notifications = Auth::user()
            ->unreadNotifications()
            ->where('type', 'App\\Notifications\\NewPermitSubmitted')
            ->latest()
            ->take(10)
            ->get()
            ->map(function ($n) {
                return [
                    'id'                => $n->id,
                    'title'             => $n->data['title'],
                    'organization_name' => $n->data['organization_name'],
                    'url'               => $n->data['url'],
                    'created_at'        => $n->created_at->diffForHumans(),
                ];
            });

        return response()->json([
            'notifications' => $notifications,
            'count'         => $notifications->count(),
        ]);
    }

    // Optional: Mark all as read
    public function markAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}
