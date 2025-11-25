<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;
use Exception;
use Crypt;
class ProfileController extends Controller
{
      public function index(Request $request)
    {
        // Start a query builder on UserProfile
        $user_profiles = UserProfile::query();

        // Apply search filter if provided
        if (!empty($request->str)) {
            $query = $request->str;

            $user_profiles->where(function($q) use ($query) {
                $q->where('first_name', 'LIKE', "%{$query}%")
                  ->orWhere('last_name', 'LIKE', "%{$query}%")
                  ->orWhere('email', 'LIKE', "%{$query}%")
                  ->orWhere('type', 'LIKE', "%{$query}%");
            });
        }

        // Get results
        $user_profiles = $user_profiles->get();

        if ($request->ajax()) {
            return view('admin.profile.profile-list', compact('user_profiles'));
        }

        return view('admin.profile.profile_list', compact('user_profiles'));
    }




    public function create()
    {
        return view('admin.profile.create_profile');
    }

    public function store(Request $request)
    {
        try{
        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'suffix'       => 'nullable|string|max:50',
            'email'        => 'required|email|max:255',
            'contact_number'=> 'required|string|max:20',
            'address'      => 'required|string|max:255',
            'sex'          => 'required|string|max:255',
            'type'         => 'required|string',
        ]);

        // Store to database
        $user_profile = UserProfile::create($validated);

        if (!$user_profile){
          throw new Exception('Unable to save the profile.');
        }
      }catch(Exception $e){
        return response()->json(['errors' => '<div class = "alert alert-danger">'.$e->getMessage().'</div>'],400);
      }
    }

    public function update(Request $request)
    {

        try{

          $id = Crypt::decryptstring($request->hiddenProfileID);
          $validated = $request->validate([
              'first_name'   => 'required|string|max:255',
              'last_name'    => 'required|string|max:255',
              'middle_name'  => 'nullable|string|max:255',
              'suffix'       => 'nullable|string|max:50',
              'email'        => 'required|email|max:255',
              'contact_number'=> 'required|string|max:20',
              'address'      => 'required|string|max:255',
              'sex'          => 'required|string|max:255',
              'type'         => 'required|string',
          ]);

        // Store to database
        $user_profile = UserProfile::where('profile_id', $id)->update($validated);

        if (!$user_profile){
          throw new Exception('Unable to save the profile.');
        }
      }catch(Exception $e){
        return response()->json(['errors' => '<div class = "alert alert-danger">'.$e->getMessage().'</div>'],400);
      }
    }
    public function edit(Request $request)
    {
      try{
        $id = Crypt::decryptstring($request->id);
        $user_profile = UserProfile::findOrFail($id);
        return response()->json($user_profile);
      }catch(Exception $e){
        return response()->json(['errors' => '<div class = "alert alert-danger">'.$e->getMessage().'</div>'],400);
      }
    }

    public function view(Request $request)
    {
      try{
        $id = Crypt::decryptstring($request->id);
        $user_profile = UserProfile::findOrFail($id);
        return response()->json($user_profile);
      }catch (Exception $e){
        return reponse()->json(['errors' => '<div class = "alert arlert-danger">'.$e->getMessage().'</div>']);
      }
    }

    public function destroy($profile_id)
    {
      try{
        $id = Crypt::decryptString(urldecode($profile_id));
        $user_profile = UserProfile::findOrFail($id);

            if ($user_profile->user) {
            $user_profile->user->delete();
        }
        $user_profile->delete();
        return response()->json(['success' => true, 'id' => $id]);
      }catch (Exception $e){
        return reponse()->json(['errors' => '<div class = "alert arlert-danger">'.$e->getMessage().'</div>']);
      }
    }

}
