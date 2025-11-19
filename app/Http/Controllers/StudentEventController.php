<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Illuminate\Support\Facades\Auth;

class StudentEventController extends Controller
{
  public function index()
  {
    $events = Event::where('organization_id', Auth::id())
      ->orderByDesc('created_at')
      ->get();

    return view('student.event.index', compact('events'));
  }
}
