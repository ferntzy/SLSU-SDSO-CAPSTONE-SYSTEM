<?php

namespace App\Http\Controllers;

use setasign\Fpdi\Fpdi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\EventApprovalFlow;
use App\Models\Permit;
use App\Models\Organization;

class SdsoheadController extends Controller
{
    //
    public function profile()
    {
        // User must be logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('sdso.profile');
    }

    public function pending()
  {
    $pendingReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->where('status', 'pending')
      ->orderBy('created_at', 'asc')
      ->get();

    return view('sdso.events.pending', compact('pendingReviews'));
  }
  public function approvals()
  {
    $pendingPermits = EventApprovalFlow::with(['permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->where('status', 'pending')
      ->orderBy('created_at', 'desc')
      ->get();

    return view('sdso.approvals', compact('pendingPermits'));
  }

  public function approved()
  {
    $approvedReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->where('status', 'approved')
      ->orderBy('approved_at', 'desc')
      ->get();

    return view('sdso.events.approved', compact('approvedReviews'));
  }

  public function history()
  {
    $historyReviews = EventApprovalFlow::with(['permit', 'permit.organization'])
      ->where('approver_role', 'SDSO_Head')
      ->whereIn('status', ['approved', 'rejected'])
      ->orderBy('updated_at', 'desc')
      ->get();

    return view('sdso.events.history', compact('historyReviews'));
  }


}
