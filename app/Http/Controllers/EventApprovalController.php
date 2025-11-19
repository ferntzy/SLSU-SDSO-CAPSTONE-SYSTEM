<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\EventApproval;
use App\Models\Signature;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;

class EventApprovalController extends Controller
{
  public function show($encryptedId)
  {
    $eventId = Crypt::decryptString($encryptedId);
    $event = Event::with('organization')->findOrFail($eventId);
    $approvals = EventApproval::where('event_id', $eventId)->get();

    return view('approvals.review', compact('event', 'approvals'));
  }

  public function review(Request $request, $encryptedId)
  {
    $eventId = Crypt::decryptString($encryptedId);
    $user = Auth::user();

    $approval = EventApproval::where('event_id', $eventId)
      ->where('approver_id', $user->user_id)
      ->firstOrFail();

    $approval->update([
      'status' => $request->input('status'),
      'comments' => $request->input('comments'),
      'approved_at' => now(),
    ]);

    // If approved, attach signature if exists
    if ($approval->status === 'approved') {
      $signature = Signature::where('user_id', $user->user_id)->first();
      if ($signature) {
        $approval->update(['signature_path' => $signature->signature_path]);
      }
    }

    // Move to next stage automatically
    if ($approval->status === 'approved') {
      $this->advanceStage($eventId);
    }

    return response()->json(['success' => true]);
  }

  private function advanceStage($eventId)
  {
    $event = Event::find($eventId);
    $stages = ['Student_Organization', 'Faculty_Adviser', 'BARGO', 'SDSO_Head', 'SAS_Director', 'VP_SAS'];
    $currentIndex = array_search($event->current_stage, $stages);

    if ($currentIndex < count($stages) - 1) {
      $event->current_stage = $stages[$currentIndex + 1];
    } else {
      $event->current_stage = 'completed';
    }

    $event->save();
  }
}
