<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event; // Assuming you have an Event model

class CalendarController extends Controller
{
  public function index()
  {
    $events = Event::all(['id', 'title', 'start', 'end', 'venue']); // customize fields as needed
    return view('admin.calendar.calendar', compact('events'));
  }

  public function store(Request $request)
  {
    $request->validate([
      'title' => 'required|string',
      'start' => 'required|date',
      'end' => 'required|date|after_or_equal:start',
      'venue' => 'required|string',
    ]);

    Event::create($request->all());

    return response()->json(['success' => true]);
  }
}
