<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Officer;

use App\Models\Member;
use App\Models\UserProfile;
use App\Models\Role;
use Illuminate\Http\Request;
use Exception;
use Crypt;
use DB;

class OrganizationController extends Controller
{
    public function index(Request $request)
    {
        $organizations = Organization::with(['adviser.profile', 'members'])
                                     ->withCount('members')
                                     ->get();

        $advisers = User::where('account_role', 'Faculty_Adviser')
                        ->with('profile')
                        ->get();

        $officers = User::where('account_role', 'Student_Organization')
                        ->with('profile')
                        ->get();

         $students = UserProfile::where('type', 'student')->get();

         $roles = Role::orderby('order')->get();

        if ($request->ajax()) {
            return view('admin.organizations.list-organization', compact('organizations'));
        }
        return view('admin.organizations.organizations', compact(
            'organizations', 'advisers', 'officers' ,'students','roles'
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
                'adviser_id' => 'required|exists:users,user_id',
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
    try {
        $id = Crypt::decryptString($request->id);
        $org = Organization::with('adviser.profile')->findOrFail($id);

        $advisers = User::where('account_role', 'faculty_adviser')
                        ->with('profile')
                        ->get();

        return response()->json([
            'organization_id'   => $org->organization_id,
            'organization_name' => $org->organization_name,
            'organization_type' => $org->organization_type,
            'description'       => $org->description,
            'adviser'           => $org->adviser?->profile ?? null,
            'advisers'          => $advisers->map(fn($a) => [
                'id'   => $a->user_id,
                'name' => trim(($a->profile->first_name ?? '') . ' ' . ($a->profile->last_name ?? ''))
            ]),
        ]);
    } catch (Exception $e) {
        return response()->json([
            'errors' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>'
        ], 400);
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




    public function add(Request $request)
    {

        try {
            $request->validate([
                'organization_id' => 'required|string', // encrypted
                'students' => 'required|array',
                'students.*' => 'exists:user_profiles,profile_id',
            ]);

            $orgId = Crypt::decryptString($request->organization_id);

            DB::transaction(function () use ($orgId, $request) {
                foreach ($request->students as $profileId) {
                        Member::firstOrCreate([
                        'organization_id' => $orgId,
                        'profile_id' => $profileId
                    ]);
                }
            });

            return response()->json([
                'success' => '<div class="alert alert-success">Organization members added successfully.</div>'
            ], 200);

        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Invalid organization ID.'
            ], 400);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ], 500);
        }
    }

    public function availableStudents(Request $request)
    {
        try {
            $orgId = Crypt::decryptString($request->id);

            $existingMembers = Member::where('organization_id', $orgId)
                                    ->pluck('profile_id')
                                    ->toArray();

            $students = UserProfile::select('profile_id','first_name','middle_name','last_name')
                        ->whereNotIn('profile_id', $existingMembers)
                        ->where('type', 'student')
                        ->get();

            return view('admin.organizations.addmember', compact('students','orgId'));
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Unable to fetch students.'
            ]);
        }
    }

    public function addOfficers($organizationId)
    {
        $org = Organization::findOrFail($organizationId);

        $students = Member::with('profile')
                    ->where('organization_id', $organizationId)
                    ->get()
                    ->map(function($m){
                        return [
                            'id' => $m->profile_id,
                            'full_name' => "{$m->profile->last_name}, {$m->profile->first_name}"
                        ];
                    });

        $roles = Role::orderBy('order')->get();

        return view('organizations.add_officer', [
            'org' => $org,
            'roles' => $roles,
            'students' => $students,
        ]);
    }


    public function saveOfficers(Request $request)
    {

        try {
            $request->validate([
            'officerOrgID'    => 'required|exists:organizations,organization_id',
            'officers'  => 'required|array',
            'officers.*' => 'nullable|exists:user_profiles,profile_id'
          ],[
            'officerOrgID.required' => "Invalid Organization selected"
          ]);

          $orgId = $request->officerOrgID;
          $naapili = false;
          foreach($request->officers as $off){
              if (!empty($off)){
                $naapili = true;
              }
          }

          if (!$naapili){
            throw new Exception("No officer selected");
          }

          $data = [];
          foreach($request->officers as $off){
            if (!empty($off)){
              $tmp = explode("|", $off);

              $data[] = [
                'organization_id' => $request->officerOrgID,
                'members_id' => $tmp[0],
                'role_id' => $tmp[1]
              ];
            }
          }

          $save = Officer::insert($data);

          if (!$save){
            throw new Exception("Officers not save.");
          }

          return response()->json([
              'success' => true,
              'message' => 'Officers saved successfully!'
          ]);

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Save Officers Error: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Failed to save: ' . $e->getMessage()
            ], 500);
        }
    }
}
