<?php

namespace App\Http\Controllers\Adviser;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\Permit;

class PermitController extends Controller
{
    public function index()
    {
        $orgIds = Auth::user()->advisedOrganizations()->pluck('organization_id');

        $permits = Permit::with(['organization', 'approvalFlow' => function($q) {
                $q->where('approver_role', 'Faculty_Adviser');
            }])
            ->whereIn('organization_id', $orgIds)
            ->latest()
            ->paginate(15);

        return view('adviser.permits.index', compact('permits'));
    }

    // This powers the notification bell
    public function notificationsData()
    {
        $notifications = DB::table('notifications')
            ->join('permits', 'notifications.message', 'like', DB::raw("CONCAT('%', permits.title_activity, '%')"))
            ->join('organizations', 'permits.organization_id', '=', 'organizations.organization_id')
            ->where('notifications.user_id', Auth::id())
            ->where('notifications.status', 'unread')
            ->where('notifications.notification_type', 'event_approval')
            ->select(
                'permits.permit_id',
                'permits.title_activity',
                'organizations.organization_name',
                'notifications.created_at'
            )
            ->latest('notifications.created_at')
            ->get();

        return response()->json(['notifications' => $notifications]);
    }
}
