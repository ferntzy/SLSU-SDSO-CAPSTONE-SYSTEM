<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VenueController extends Controller
{
    /**
     * Display a listing of venues
     */
    public function index()
    {
        $venues = DB::table('venues')
            ->select('venue_id', 'venue_name', 'created_at', 'updated_at')
            ->orderBy('venue_name', 'asc')
            ->get();

        return view('venues.index', compact('venues'));
    }

    /**
     * Show the form for creating a new venue
     */
    public function create()
    {
        return view('venues.create');
    }

    /**
     * Store a newly created venue in the database
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'venue_name' => 'required|string|max:255|unique:venues,venue_name',
        ]);

        DB::table('venues')->insert([
            'venue_name' => $validated['venue_name'],
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('venues.index')
            ->with('success', 'Venue created successfully!');
    }

    /**
     * Show the form for editing a venue
     */
    public function edit($id)
    {
        $venue = DB::table('venues')
            ->where('venue_id', $id)
            ->first();

        if (!$venue) {
            return redirect()->route('venues.index')
                ->with('error', 'Venue not found.');
        }

        return view('venues.edit', compact('venue'));
    }

    /**
     * Update the specified venue in the database
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'venue_name' => 'required|string|max:255|unique:venues,venue_name,' . $id . ',venue_id',
        ]);

        $updated = DB::table('venues')
            ->where('venue_id', $id)
            ->update([
                'venue_name' => $validated['venue_name'],
                'updated_at' => now(),
            ]);

        if ($updated) {
            return redirect()->route('venues.index')
                ->with('success', 'Venue updated successfully!');
        }

        return redirect()->route('venues.index')
            ->with('error', 'Failed to update venue.');
    }

    /**
     * Remove the specified venue from the database
     */
    public function destroy($id)
    {
        // Check if venue is being used in permits
        $usedInPermits = DB::table('permits')
            ->where('venue', $id)
            ->where('type', 'In-Campus')
            ->count();

        if ($usedInPermits > 0) {
            return redirect()->route('venues.index')
                ->with('error', 'Cannot delete venue. It is being used in ' . $usedInPermits . ' permit(s).');
        }

        DB::table('venues')->where('venue_id', $id)->delete();

        return redirect()->route('venues.index')
            ->with('success', 'Venue deleted successfully!');
    }

    /**
     * API endpoint - Get all venues as JSON
     */
    public function getVenues()
    {
        $venues = DB::table('venues')
            ->select('venue_id', 'venue_name')
            ->orderBy('venue_name', 'asc')
            ->get();

        return response()->json($venues);
    }

    /**
     * API endpoint - Get single venue
     */
    public function getVenue($id)
    {
        $venue = DB::table('venues')
            ->select('venue_id', 'venue_name')
            ->where('venue_id', $id)
            ->first();

        if (!$venue) {
            return response()->json(['error' => 'Venue not found'], 404);
        }

        return response()->json($venue);
    }
}
