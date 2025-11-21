<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Officer;
use App\Models\UserProfile;
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
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_type' => 'required|string|max:100',
            'description' => 'nullable|string',
        ]);

        Organization::create([
            'organization_name' => $request->organization_name,
            'organization_type' => $request->organization_type,
            'description' => $request->description,
            'status' => 'Active', // default status
        ]);

        return redirect()->back()->with('success', 'Organization added successfully!');
    }
    public function update(Request $request, Organization $organization)
    {
        $request->validate([
            'organization_name' => 'required|string|max:255',
            'organization_type' => 'required|string',
            'description' => 'nullable|string',
        ]);

        // Update organization fields
        $organization->organization_name = $request->organization_name;
        $organization->organization_type = $request->organization_type;
        $organization->description = $request->description;

        // ========= SAVE ADVISER =========
        if ($request->adviser_id) {
            $organization->user_id = $request->adviser_id; // adviser stored here
        }

        $organization->save();

        // ========= SAVE OFFICER =========
        if ($request->officer_id) {

            // find the student’s profile_id
            $profileId = User::where('user_id', $request->officer_id)->value('profile_id');

            Officer::updateOrCreate(
                [
                    'organization_id' => $organization->organization_id,
                    'user_id' => $request->officer_id
                ],
                [
                    'profile_id' => $profileId,
                    'role' => 'officer'
                ]
            );
        }

        return redirect()->back()->with('success', 'Organization updated successfully!');
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
