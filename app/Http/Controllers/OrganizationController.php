<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Http\Request;

class OrganizationController extends Controller
{
    public function index()
    {
        // Load organizations with members + adviser detection
        $organizations = Organization::with(['adviser.profile', 'members'])
                                     ->withCount('members')
                                     ->get();

        // Advisers list (for dropdown if needed)
        $advisers = User::where('account_role', 'Faculty_Adviser')
                        ->with('profile')
                        ->get();

        // Student organization officers
        $officers = User::where('account_role', 'Student_Organization')
                        ->with('profile')
                        ->get();

        return view('admin.organizations.organizations', compact(
            'organizations', 'advisers', 'officers'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_type' => 'required|string|max:255',
            'description'       => 'nullable|string',
            'contact_email'     => 'nullable|email',
            'contact_number'    => 'nullable|string|max:20',
            'profile_id'        => 'required|integer',
        ]);

        // creator user
        $validated['user_id'] = auth()->id();

        $organization = Organization::create($validated);

        // Create officer record if selected
        if (!empty($validated['officer_id'])) {
            $organization->officers()->create([
                'user_id' => $validated['officer_id'],
                'role'    => 'Officer',
                'profile_id' => $request->profile_id, // <-- include here
            ]);
        }

        return back()->with('success', 'Organization added successfully!');
    }

    public function show($organization_id)
    {
        $org = Organization::with([
                'adviser.profile',
                'officers.user.profile',
            ])
            ->withCount('members')
            ->findOrFail($organization_id);

        $officer = $org->officers->first()?->user?->profile;

        return response()->json([
            'organization_name' => $org->organization_name,
            'organization_type' => $org->organization_type,
            'description'       => $org->description,
            'members_count'     => $org->members_count,
            'adviser'           => $org->advisor_name,
            'status'            => $org->status,
            'created_at'        => $org->created_at->format('F d, Y'),

            // Officer details
            'officer_name'      => $officer?->full_name,
            'contact_number'    => $officer?->contact_number,
            'contact_email'     => $officer?->contact_email,
        ]);
    }

    public function destroy($organization_id)
    {
        $org = Organization::find($organization_id);

        if (!$org) {
            return response()->json([
                'success' => false,
                'message' => 'Organization not found'
            ]);
        }

        $org->delete();

        return response()->json([
            'success' => true,
            'message' => 'Organization deleted successfully'
        ]);
    }
}
