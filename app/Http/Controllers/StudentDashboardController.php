<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class StudentDashboardController extends Controller
{
  public function index()
  {
    $userId = Auth::id();

    // Fetch related organization
    $organization = auth()->user()->organization;

    // Stats
    $pendingEvents = Event::where('organization_id', $userId)->where('proposal_status', 'pending')->count();
    $approvedEvents = Event::where('organization_id', $userId)->where('proposal_status', 'approved')->count();
    $rejectedEvents = Event::where('organization_id', $userId)->where('proposal_status', 'rejected')->count();

    // Upcoming Events
    $upcomingEvents = Event::where('organization_id', $userId)
      ->whereDate('event_date', '>=', now())
      ->orderBy('event_date', 'asc')
      ->take(5)
      ->get();

    // ✅ Include $organization in compact
    return view('student.dashboard', compact(
      'pendingEvents',
      'approvedEvents',
      'rejectedEvents',
      'upcomingEvents',
      'organization'
    ));
  }
}
