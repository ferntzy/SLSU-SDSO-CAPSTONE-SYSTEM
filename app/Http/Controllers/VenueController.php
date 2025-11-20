<?php

namespace App\Http\Controllers;

use App\Models\Venue;
use Illuminate\Http\Request;

class VenueController extends Controller
{
    public function index()
    {
        // Get all venues for the In-Campus dropdown
        $venues = Venue::orderBy('venue_name')->get();

        // Return the view with venues
        return view('students.calendardisplay', compact('venues'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'venue_name' => 'required|string|max:255',
        ]);

        Venue::create([
            'venue_name' => $request->venue_name,
        ]);

        return back()->with('success', 'Venue added successfully!');
    }
}
