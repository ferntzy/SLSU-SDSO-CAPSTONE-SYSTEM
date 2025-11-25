<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Officer;
use App\Models\UserProfile;
use Illuminate\Http\Request;
use Exception;

class OrganizationController extends Controller
{
    public function index(Request $request)
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

        if ($request->ajax()) {
            return view('admin.organizations.list-organization', compact('organizations'));
        }
        return view('admin.organizations.organizations', compact(
            'organizations', 'advisers', 'officers'
        ));
    }
    public function create()
    {
        return view ('admin.organizations.create-organization');
    }

    public function store(Request $request)
    {
        try {
            // ===== VALIDATION =====
            $request->validate([
                'organization_name' => 'required|string|max:255',
                'organization_type' => 'required|string',
                'description' => 'nullable|string',
                'adviser_id' => 'required|exists:users,user_id',  // adviser is required
                'officer_id' => 'required|exists:users,user_id',  // officer is required
                'organization_logo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            ]);

            // ===== CREATE ORGANIZATION =====
            $organization = new Organization();
            $organization->organization_name = $request->organization_name;
            $organization->organization_type = $request->organization_type;
            $organization->description = $request->description;
            $organization->user_id = $request->adviser_id; // adviser is required
            $organization->save();

            if (!$organization->organization_id) {
                throw new Exception('Unable to save organization.');
            }

            // ===== CREATE OFFICER =====
            $officerUser = User::findOrFail($request->officer_id);
            Officer::create([
                'organization_id' => $organization->organization_id,
                'user_id' => $officerUser->user_id,
                'profile_id' => $officerUser->profile_id,
                'role' => 'officer',
            ]);

            return response()->json([
                'success' => '<div class="alert alert-success">Organization and officer created successfully.</div>'
            ], 200);

        } catch (Exception $e) {
            return response()->json([
                'errors' => '<div class="alert alert-danger">'.$e->getMessage().'</div>'
            ], 400);
        }
    }
    public function update(Request $request)
    {

    }
    public function edit(Request $request)
    {
      try{
        $id = Crypt::decryptString($request->id);
        $org = Organization::findOrFail($id);
        return response()->json($org);
      }catch(Exception $e){
        return response()->json(['errors' => '<div class = "alert alert-danger">'.$e->getMessage().'</div>'],400);
      }
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
