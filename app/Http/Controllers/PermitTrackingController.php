<?php

namespace App\Http\Controllers;

use App\Models\Permit;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Report;          // ← add this too
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
class PermitTrackingController extends Controller
{
  /**
   * Get the organization ID that the authenticated user belongs to
   * Uses: users → user_profiles → members → organizations
   */
  private function getUserOrganizationId()
  {
    $user = auth()->user();

    // THIS IS THE MAGIC LINE — gets profile_id directly from DB, bypassing Laravel cache
    $profileId = \DB::table('users')->where('user_id', $user->user_id)->value('profile_id');

    if (!$profileId) {
      abort(403, 'Your account has no linked profile.');
    }

    $member = \DB::table('members')
      ->where('profile_id', $profileId)
      ->first();

    if (!$member) {
      // Optional: auto-add for testing (remove later if you want)
      \DB::table('members')->insert([
        'profile_id' => $profileId,
        'organization_id' => 9,
        'membership_status' => 'active',
        'joined_at' => now(),
        'updated_at' => now(),
      ]);
      return 9;
    }

    return $member->organization_id;
  }

  /**
   * Base query for permits of the user's organization
   */
  private function getOrganizationPermitsQuery()
  {
    $orgId = $this->getUserOrganizationId();

    return Permit::query()
      ->with(['approvalFlow' => fn($q) => $q->orderBy('created_at')])
      ->with(['approvalFlow.approver.profile'])
      ->where('organization_id', $orgId)
      ->latest('created_at');
  }

  /**
   * Share permit counts in sidebar (cached for 5 minutes)
   */
  private function sharePermitCounts()
  {
    if (View::shared('pendingPermitsCount') !== null) return;

    $orgId = $this->getUserOrganizationId();

    $counts = Cache::remember("org_{$orgId}_permit_counts", 300, function () use ($orgId) {
      $permits = Permit::with('approvalFlow')->where('organization_id', $orgId)->get();

      $pending = $approved = $rejected = 0;

      foreach ($permits as $permit) {
        $flow = $permit->approvalFlow;

        $hasRejected = $flow->contains('status', 'rejected');
        $hasPending = $flow->isEmpty() || $flow->contains('status', 'pending');
        $vpApproved = $flow->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty();

        if ($hasRejected) {
          $rejected++;
        } elseif ($vpApproved && !$hasPending) {
          $approved++;
        } else {
          $pending++;
        }
      }

      return compact('pending', 'approved', 'rejected');
    });

    View::share([
      'pendingPermitsCount'   => $counts['pending'],
      'approvedPermitsCount'  => $counts['approved'],
      'rejectedPermitsCount' => $counts['rejected'],
    ]);
  }

  // ==================================================================
  // MAIN TRACKING DASHBOARD
  // ==================================================================
  public function track()
  {

    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()->get();
    $now = now();

    $pendingPermits = $permits->filter(
      fn($p) => ($p->approvalFlow->isEmpty() || $p->approvalFlow->contains('status', 'pending')) &&
        !$p->approvalFlow->contains('status', 'rejected')
    );

    $rejectedPermits = $permits->filter(fn($p) => $p->approvalFlow->contains('status', 'rejected'));

    $approvedPermits = $permits->filter(
      fn($p) =>
      $p->approvalFlow->where('approver_role', 'VP_SAS')->where('status', 'approved')->isNotEmpty() &&
        !$p->approvalFlow->whereIn('status', ['pending', 'rejected'])->count()
    );

    $ongoingEvents = $approvedPermits->filter(function ($p) use ($now) {
      $start = $p->date_start ? \Carbon\Carbon::parse($p->date_start) : null;
      $end   = $p->date_end ? \Carbon\Carbon::parse($p->date_end) : null;

      // Must have started
      if (!$start || $start->gt($now)) {
        return false;
      }

      // If has end date → must not have ended yet
      // If no end date → consider ongoing as long as it has started
      if ($end) {
        return $end->endOfDay() >= $now; // includes today
      }

      return true; // no end date → still ongoing
    });

    // Successful: Approved + has ended (has end date AND end date passed)
    $successfulEvents = $approvedPermits->filter(function ($p) use ($now) {
      $end = $p->date_end ? \Carbon\Carbon::parse($p->date_end) : null;
      return $end && $end->endOfDay() < $now;
    });

    return view('student.permit.tracking', compact(
      'pendingPermits',
      'approvedPermits',
      'rejectedPermits',
      'ongoingEvents',
      'successfulEvents'
    ));
  }

  // ==================================================================
  // INDIVIDUAL PAGES
  // ==================================================================
  public function pendingPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->where(function ($q) {
        $q->whereHas('approvalFlow', fn($sq) => $sq->where('status', 'pending'))
          ->orWhereDoesntHave('approvalFlow');
      })
      ->whereDoesntHave('approvalFlow', fn($q) => $q->where('status', 'rejected'))
      ->paginate(10);

    return view('student.page.pending', compact('permits'));
  }

  public function approvedPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->whereHas(
        'approvalFlow',
        fn($q) =>
        $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
      )
      ->whereDoesntHave(
        'approvalFlow',
        fn($q) =>
        $q->whereIn('status', ['pending', 'rejected'])
      )
      ->paginate(10);

    return view('student.page.approved', compact('permits'));
  }

  public function rejectedPage()
  {
    $this->sharePermitCounts();

    $permits = $this->getOrganizationPermitsQuery()
      ->whereHas('approvalFlow', fn($q) => $q->where('status', 'rejected'))
      ->paginate(10);

    return view('student.page.rejected', compact('permits'));
  }

  // Legacy redirects
  public function index()
  {
    return $this->track();
  }
  public function pending()
  {
    return $this->pendingPage();
  }
  public function approved()
  {
    return $this->approvedPage();
  }
  public function rejected()
  {
    return $this->rejectedPage();
  }
  public function successfulPage()
  {
    $this->sharePermitCounts();

    $now = now();

    $successfulEvents = $this->getOrganizationPermitsQuery()
      ->whereNotNull('date_end')
      ->where('date_end', '<', $now->endOfDay())
      ->whereHas(
        'approvalFlow',
        fn($q) =>
        $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
      )
      ->whereDoesntHave(
        'approvalFlow',
        fn($q) =>
        $q->whereIn('status', ['pending', 'rejected'])
      )
      ->with(['reports' => fn($q) => $q->latest()])
      ->paginate(10);

    return view('student.page.successful', compact('successfulEvents'));
  }
public function storeReport(Request $request)
{
    $request->validate([
        'permit_id'     => 'required|integer|exists:permits,permit_id',
        'event_id'      => 'nullable|integer',
        'document_type' => 'required|in:minutes,photos,report,certificate,other',
        'title'         => 'nullable|string|max:255',
        'description'   => 'nullable|string',
        'documents.*'   => 'required|file|mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx,ppt,pptx|max:20480',
    ]);

    // Verify ownership
    $permit = \App\Models\Permit::where('permit_id', $request->permit_id)
        ->whereIn('organization_id', function($q) {
            $q->select('off.organization_id')
              ->from('officers as off')
              ->join('members as m', 'off.members_id', '=', 'm.member_id')
              ->join('user_profiles as up', 'm.profile_id', '=', 'up.profile_id')
              ->join('users as u', 'up.profile_id', '=', 'u.profile_id')
              ->where('u.user_id', auth()->id());
        })
        ->firstOrFail();

    if ($permit->is_completed) {
        return back()->with('swal', [
            'title' => 'Error',
            'text'  => 'This event is already submitted.',
            'icon'  => 'error'
        ]);
    }

    foreach ($request->file('documents') as $file) {
        $folder = "permits/offcampus/{$permit->permit_id}";
        $name   = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $ext    = $file->getClientOriginalExtension();
        $filename = \Str::slug($name) . '_' . time() . \Str::random(6) . '.' . $ext;

        $path = $file->storeAs($folder, $filename, 'public');

        Report::create([
            'permit_id'         => $permit->permit_id,        // ← NOW SAVED!
            'event_id'          => $request->event_id ?? null,
            'document_type'     => $request->document_type,
            'title'             => $request->title ?? $file->getClientOriginalName(),
            'description'       => $request->description,
            'document_url'      => $path,
            'original_filename' => $file->getClientOriginalName(),
            'mime_type'         => $file->getClientMimeType(),
            'file_size'         => $file->getSize(),
        ]);
    }

    return back()->with('swal', [
        'title' => 'Success!',
        'text'  => 'Files uploaded successfully!',
        'icon'  => 'success',
        'timer' => 2500
    ]);
}
  public function submissionsHistory()
{
    $this->sharePermitCounts(); // assuming this shares $pendingPermits, etc.

    return view('student.page.submissions-history');
}
  public function ongoingPage()
  {
    $this->sharePermitCounts();

    $now = now();

    $ongoingEvents = $this->getOrganizationPermitsQuery()
      ->whereNotNull('date_start')
      ->where('date_start', '<=', $now)
      ->where(function ($q) use ($now) {
        $q->whereNull('date_end')
          ->orWhere('date_end', '>=', $now->startOfDay());
      })
      ->whereHas(
        'approvalFlow',
        fn($q) =>
        $q->where('approver_role', 'VP_SAS')->where('status', 'approved')
      )
      ->whereDoesntHave(
        'approvalFlow',
        fn($q) =>
        $q->whereIn('status', ['pending', 'rejected'])
      )
      ->latest('date_start')
      ->paginate(15);

    return view('student.page.ongoing', compact('ongoingEvents'));
  }
  public function showReports($hashed_id)
  {
    try {
      $permitId = Crypt::decryptString($hashed_id);
    } catch (\Exception $e) {
      abort(404);
    }
    $permit = Permit::with('reports')->findOrFail($permitId);

    if ($permit->organization_id !== $this->getUserOrganizationId()) {
      abort(403);
    }

    return view('student.reports.show', compact('permit'));
  }
  public function markAsCompleted(Request $request, Permit $permit)
  {
    if ($permit->organization_id !== $this->getUserOrganizationId()) {
      abort(403);
    }

    $permit->update([
      'is_completed' => true,
      'completed_at' => now(),
    ]);

    return back()->with('success', 'Event marked as completed! It has been moved to Successful Events.');
  }
  // In PermitTrackingController.php
  public function submitToSdso(Request $request, $hashed_id)
  {
    try {
      $permitId = Crypt::decryptString($hashed_id);
    } catch (\Exception $e) {
      return back()->with('swal', [
        'title' => 'Error!',
        'text'  => 'Invalid link.',
        'icon'  => 'error'
      ]);
    }

    $permit = \App\Models\Permit::find($permitId);

    if (!$permit) {
      return back()->with('swal', ['title' => 'Not Found', 'text' => 'Event not found.', 'icon' => 'error']);
    }

    // THIS IS THE REAL FIX — check via the organization relationship
    $user = auth()->user();

    // Option A (most common): user has a direct organization relationship
    if ($user->organization && $user->organization->id !== $permit->organization_id) {
      return back()->with('swal', [
        'title' => 'Access Denied',
        'text'  => 'You do not own this event.',
        'icon'  => 'warning'
      ]);
    }

    // Option B (fallback): if user has organization_id directly on users table
    // Remove this line completely if your users table doesn't have organization_id
    // if ($user->organization_id && $user->organization_id !== $permit->organization_id) { ... }

    // No files check
    if ($permit->reports()->count() === 0) {
      return back()->with('swal', [
        'title' => 'No Files',
        'text'  => 'Please upload at least one file first.',
        'icon'  => 'info'
      ]);
    }

    // SUCCESS — update database
    $permit->update([
      'is_completed' => 1,
      'completed_at' => now(),
    ]);

    return back()->with('swal', [
      'title' => 'Submitted!',
      'text'  => 'Documentation successfully sent to SDSO!',
      'icon'  => 'success',
      'timer' => 3000
    ]);
  }
  public function download($hashed_id)
  {
    $permit = Permit::where('hashed_id', $hashed_id)->firstOrFail();

    // Make sure your view() method returns proper PDF headers
    return $this->view($permit->hashed_id); // or however you generate PDF
  }
}
