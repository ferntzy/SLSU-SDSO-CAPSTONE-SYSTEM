<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Permit;
use Carbon\Carbon;

class PermitTrackingController extends Controller
{
    // Main tracking page
    public function index()
    {
        $permits = Permit::with('organization', 'approvals')
            ->where('organization_id', auth()->id())
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.permit.tracking', compact('permits'));
    }

    // Pending permits
    public function pending()
    {
        $permits = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->whereHas('approvals', function ($q) {
                $q->where('status', 'pending');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permit.pending', compact('permits'));
    }

    // Approved permits
    public function approved()
    {
        $permits = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->whereDoesntHave('approvals', function ($q) {
                $q->whereIn('status', ['pending', 'rejected']);
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permit.approved', compact('permits'));
    }

    // Rejected permits
    public function rejected()
    {
        $permits = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->whereHas('approvals', function ($q) {
                $q->where('status', 'rejected');
            })
            ->orderBy('created_at', 'desc')
            ->get();

        return view('permit.rejected', compact('permits'));
    }

    // Ongoing events
    public function ongoingEvents()
    {
        $today = Carbon::now()->format('Y-m-d');

        $events = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->where('date_start', '<=', $today)
            ->where(function($q) use ($today) {
                $q->whereNull('date_end')
                  ->orWhere('date_end', '>=', $today);
            })
            ->orderBy('date_start', 'asc')
            ->get();

        return view('events.ongoing', compact('events'));
    }

    // Successful events
    public function successfulEvents()
    {
        $today = Carbon::now()->format('Y-m-d');

        $events = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->where('date_end', '<', $today)
            ->whereDoesntHave('approvals', function($q){
                $q->where('status', 'rejected');
            })
            ->orderBy('date_end', 'desc')
            ->get();

        return view('events.successful', compact('events'));
    }

    // Canceled events
    public function canceledEvents()
    {
        $events = Permit::with('organization', 'approvals')
            ->where('user_id', auth()->id())
            ->whereHas('approvals', function($q){
                $q->where('status', 'rejected');
            })
            ->orderBy('date_start', 'desc')
            ->get();

        return view('events.canceled', compact('events'));
    }

    // View PDF
    public function viewPDF($id)
    {
        $permit = Permit::where('hashed_id', $id)
            ->where('organization_id', auth()->id())
            ->firstOrFail();

        return view('permit.pdf_view', compact('permit'));
    }

    // Generate permit (Ajax)
    public function generate(Request $request)
    {
        // Example: Save permit and return JSON response
        // Validation & saving logic here...
        return response()->json([
            'status' => 'success',
            'message' => 'Permit successfully generated.'
        ]);
    }
}
