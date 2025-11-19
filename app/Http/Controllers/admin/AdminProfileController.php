<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\UserProfile;

class AdminProfileController extends Controller
{

public function update(Request $request)
{
    $user = Auth::user();

    $validated = $request->validate([
        'first_name' => 'required|string|max:100',
        'middle_name' => 'required|string|max:100',
        'last_name' => 'required|string|max:100',
        'contact_number' => 'nullable|string|max:20',
        'address' => 'nullable|string|max:255',
        'office' => 'required|string|max:100',
        'profile_picture_path' => 'nullable|image|mimes:jpg,jpeg,png|max:10240',
    ]);

    if ($request->hasFile('profile_picture_path')) {

        $file = $request->file('profile_picture_path');
        $filename = time() . '_' . $file->getClientOriginalName();

        // Use role name directly
        $roleFolder = $user->account_role; // admin OR student_org

        // Example:
        // images/user_profile/admin/15/
        // images/user_profile/student_org/22/
        $destinationPath = public_path("images/user_profile/{$roleFolder}/{$user->user_id}");

        if (!file_exists($destinationPath)) {
            mkdir($destinationPath, 0777, true);
        }

        $file->move($destinationPath, $filename);

        $validated['profile_picture_path'] = "images/user_profile/{$roleFolder}/{$user->user_id}/{$filename}";
    }

    UserProfile::updateOrCreate(
        ['user_id' => $user->user_id],
        $validated
    );

    return redirect()->route('admin.profile.show')->with('success', 'Profile updated successfully!');
}


    public function profile() {
    $admin = Auth::user();
    return view('admin.profile.admin_profile', compact('admin'));
}

}
