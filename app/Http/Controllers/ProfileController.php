<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\UserProfile;

class ProfileController extends Controller
{
    public function index()
    {
      $user_profiles = UserProfile::all(); // fetch all profiles
      return view('admin.profile.profile_list', compact('user_profiles'));
    }
    public function create()
    {
        return view('admin.profile.create_profile');
    }
    public function store(Request $request)
    {
        // dd($request->all());
        // Validate the fields
        $validated = $request->validate([
            'first_name'   => 'required|string|max:255',
            'last_name'    => 'required|string|max:255',
            'middle_name'  => 'nullable|string|max:255',
            'suffix'       => 'nullable|string|max:50',
            'email'        => 'required|email|max:255',
            'contact_number'=> 'required|string|max:20',
            'address'      => 'required|string|max:255',
            'sex'          => 'required|string',
            'type'         => 'required|string',
        ]);

        // Store to database
        $user_profile = UserProfile::create($validated);

        return redirect()->route('profiles.index')
        ->with('success', 'Profile created successfully!')
        ->with('highlight_id', $user_profile->profile_id);
    }
}
