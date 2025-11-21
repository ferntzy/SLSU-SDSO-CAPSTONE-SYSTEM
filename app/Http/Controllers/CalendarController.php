<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\Permit; // Assuming Permit is your main activity/event model
use App\Models\Organization;
use App\Models\Venue;
use Illuminate\Support\Facades\Log; // For debugging
use Illuminate\Support\Carbon; // Use Carbon for date manipulation

class CalendarController extends Controller
{
    /**
     * Display the main calendar view.
     */
    public function index()
    {
        // This function loads the Blade file containing the FullCalendar setup (student.calendardisplay)
        return view('student.calendardisplay');
    }

    /**
     * Fetch events (Permits) from the database for FullCalendar (GET /student/calendar/events).
     * Now filters events to ensure they are 'approved' and are current or future.
     */
    public function getEvents()
    {
        // Get today's date for filtering
        $today = Carbon::today()->toDateString();

        // Fetch only approved permits that are currently ongoing or in the future
        $events = Permit::where('status', 'approved')
            // The event is relevant if its end date is today or later.
            // If date_end is null (single-day event), we check date_start >= today.
            ->where(function ($query) use ($today) {
                // Case 1: Multi-day event whose end date is today or in the future
                $query->whereNotNull('date_end')
                      ->where('date_end', '>=', $today);

                // Case 2: Single-day event (date_end is null/same as start) that starts today or in the future
                $query->orWhere(function ($q) use ($today) {
                    $q->where('date_start', '>=', $today);
                });
            })
            ->with('organization') // Ensure organization relationship is loaded
            ->get();

        // Transform data into FullCalendar's required array structure
        $formattedEvents = $events->map(function ($event) {
            // FullCalendar requires date_start/date_end to include time if available
            // Note: The time format in DB seems to be 24-hour (H:i), matching the client-side flatpickr settings.
            $startDateTime = $event->date_start . ' ' . $event->time_start;
            // Ensure end time uses end date, falling back to start date if end date is null
            $endDateTime = $event->date_end ? ($event->date_end . ' ' . $event->time_end) : ($event->date_start . ' ' . $event->time_end);

            return [
                'id' => $event->id,
                'title' => $event->title_activity,
                'start' => $startDateTime,
                'end' => $endDateTime,
                'allDay' => false,
                'backgroundColor' => '#32C5D2', // Example color: bright cyan for approved events
                'borderColor' => '#32C5D2',
                'extendedProps' => [
                    'organization_name' => $event->organization->organization_name ?? 'N/A',
                    'venue' => $event->venue,
                    'purpose' => $event->purpose,
                ]
            ];
        });

        return response()->json($formattedEvents);
    }

    /**
     * Render the raw permit form content (GET /student/calendar/permit/form-content).
     * This is fetched via AJAX to populate the calendar modal body.
     */
    public function showPermitFormContent(Request $request)
    {
        // Optional: Pre-fill dates from FullCalendar selection if available in request
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        // Fetch necessary data for the form dropdowns
        $organizations = Organization::all();
        $venues = Venue::all(); // Assuming a Venue model for in-campus locations

        // Return the dedicated Blade file containing ONLY the form HTML content
        return view('student.permit.form_content', compact('organizations', 'venues', 'startDate', 'endDate'));
    }

    /**
     * Store the new event/permit request (POST /student/calendar/events/store).
     */
    public function storeEvent(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'organization_id' => 'required|exists:organizations,id',
            // VALIDATION KEY MISMATCH FIX: Renamed 'title_activity' to 'event_title'
            'event_title' => 'required|string|max:255',
            'purpose' => 'required|string',
            'type' => 'required|in:In-Campus,Off-Campus',
            'venue' => 'required|string|max:255', // This is the 'final_venue_name' hidden field
            'date_start' => 'required|date',
            // date_end is optional
            'date_end' => 'nullable|date|after_or_equal:date_start',
            'time_start' => 'required|date_format:H:i', // Changed format to 24-hr as per form JS
            'time_end' => 'required|date_format:H:i|after:time_start', // Changed format to 24-hr as per form JS
            'nature' => 'required|string|max:255',
            'participants' => 'required|string|max:255',
            // VALIDATION KEY MISMATCH FIX: Renamed 'number' to 'attendees_count'
            'attendees_count' => 'required|integer|min:1',
            'signature_data' => 'nullable|string',
            'signature_upload' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // Ensure either signature data or an uploaded file exists
        if (!$request->filled('signature_data') && !$request->hasFile('signature_upload')) {
             return response()->json(['success' => false, 'message' => 'Signature is required.'], 422);
        }

        try {
            // Get data fields that match Permit model column names
            $eventData = [
                'user_id' => Auth::id(),
                'organization_id' => $request->organization_id,
                'event_title' => $request->event_title,
                'purpose' => $request->purpose,
                'nature' => $request->nature,
                'type' => $request->type,
                'final_venue_name' => $request->venue, // Map 'venue' (hidden field) to 'final_venue_name'
                'venue_id' => $request->venue_id, // This is optional
                'date_start' => $request->date_start,
                'date_end' => $request->date_end,
                'time_start' => $request->time_start,
                'time_end' => $request->time_end,
                'participants' => $request->participants,
                'attendees_count' => $request->attendees_count,
                'proposal_status' => 'pending', // Set initial status
                'current_stage' => 'Student_Organization', // Set initial stage
                'event_report_submitted' => 0,
                'event_permit_submitted' => 1,
                // The 'name' field from the form is applicant name, may need dedicated column
            ];

            // 1. Handle Signature Upload
            if ($request->filled('signature_data')) {
                // Drawn signature (Base64)
                $signature = $request->input('signature_data');
                // Clean the data URI prefix
                $signature = str_replace('data:image/png;base64,', '', $signature);
                $signature = str_replace(' ', '+', $signature);
                $signatureImage = base64_decode($signature);

                $filename = 'signatures/' . Str::uuid() . '.png';
                // FIX: Use the default Storage disk which is typically configured correctly
                Storage::put($filename, $signatureImage);
                $eventData['signature_path'] = $filename;
            } elseif ($request->hasFile('signature_upload')) {
                // File upload signature
                // FIX: Use the default Storage disk
                $path = $request->file('signature_upload')->store('signatures');
                $eventData['signature_path'] = $path;
            }

            // 2. Create the Permit record
            Permit::create($eventData);

            return response()->json([
                'success' => true,
                'message' => 'Activity Permit submitted successfully for review!',
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
             return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Permit Submission Error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return response()->json([
                'success' => false,
                'message' => 'A server error occurred during submission: ' . $e->getMessage(),
            ], 500);
        }
    }
}
