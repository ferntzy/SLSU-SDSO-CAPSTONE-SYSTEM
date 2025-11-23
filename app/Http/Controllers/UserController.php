<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserProfile;
use Exception;
use Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
 public function index(Request $request)
    {
      $user_accounts = User::with('profile');

      if (!empty($request->str)) {
          $query = $request->str;

          $user_accounts = $user_accounts->where(function($q) use ($query) {
              $q->where('username', 'LIKE', "%{$query}%")
                ->orWhereHas('profile', function($q2) use ($query) {
                    $q2->where('first_name', 'LIKE', "%{$query}%")
                      ->orWhere('last_name', 'LIKE', "%{$query}%")
                      ->orWhere('type', 'LIKE', "%{$query}%");
                });
          });
      }

      $user_accounts = $user_accounts->get();

      $user_profiles_student = UserProfile::where('type','student')->orderby('last_name')->orderby('first_name')->get();  // <-- ADD THIS
      $user_profiles_employee = UserProfile::where('type','employee')->orderby('last_name')->orderby('first_name')->get();
      if ($request->ajax()){
        return view('admin.users.index-list', compact('user_accounts'));
      }

    return view('admin.users.index', compact('user_accounts', 'user_profiles_student','user_profiles_employee'));
    }


public function create()
{

    return view('admin.users.create');
}



  public function store(Request $request)
    {
        try {
            // Validate inputs

            $validated = $request->validate([
                'username'     => 'required|unique:users,username',
                'password'     => 'required|min:6|confirmed',
                'account_role' => 'required',
                'profile_id'   => 'required',
            ]);

            // Hash PASSWORD
            $validated['password'] = Hash::make($validated['password']);

            // Save
            $user = User::create($validated);

            if (!$user) {
                throw new Exception("Unable to save the user.");
            }

            return response()->json(['success' => true]);

        } catch (Exception $e) {
            return response()->json([
                'errors' => '<div class="alert alert-danger">'.$e->getMessage().'</div>'
            ], 400);
        }
    }


    public function checkUsername(Request $request)
      {
          $exists = User::where('username', $request->username)->exists();

          return response()->json(['exists' => $exists]);
      }








    public function update(Request $request, User $user)
    {

        try{

          $id = Crypt::decryptstring($request->hiddenAccountID);
          $validated = $request->validate([
              'username'   => 'required|string|max:255',
              'account_role'   => 'required|string|max:255',
              'password'  => 'nullable|min:6',

          ]);

        // Store to database
        $user_account = User::where('user_id', $id)->update($validated);

        if (!$user_account){
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
        $user_account = UserProfile::findOrFail($id);
        return response()->json($user_account);
      }catch(Exception $e){
        return response()->json(['errors' => '<div class = "alert alert-danger">'.$e->getMessage().'</div>'],400);
      }
    }


  public function viewProfile($id)
{
    $user = User::with('profile')->findOrFail($id);

    return view('admin.users.partials.view-profile', compact('user'));
}



public function search(Request $request)
{
    $query = $request->get('query', '');

    $user_accounts = User::with('profile')
        ->where('username', 'LIKE', "%{$query}%")
        ->orWhereHas('profile', function($q) use ($query){
            $q->where('first_name', 'LIKE', "%{$query}%")
              ->orWhere('last_name', 'LIKE', "%{$query}%")
              ->orWhere('type', 'LIKE', "%{$query}%");
        })
        ->orWhere('account_role', 'LIKE', "%{$query}%")
        ->orderBy('created_at', 'desc')
        ->get();

    if($user_accounts->count()){
        return view('admin.users.partials.userlist', compact('user_accounts'))->render();
    } else {
        return '<tr><td colspan="8" class="text-center text-muted">No Profiles found.</td></tr>';
    }
}











  //check user and email if already use - lex
  public function checkAvailability(Request $request)
{
    $field = $request->input('field'); // username or email
    $value = $request->input('value');

    if (!in_array($field, ['username', 'email'])) {
        return response()->json(['error' => 'Invalid field'], 422);
    }

    $exists = \App\Models\User::where($field, $value)->exists();

    return response()->json([
        'available' => !$exists
    ]);
}



public function destroy(Request $request, $id)
{
    $admin = auth()->user();

    if ($admin->account_role !== 'admin') {
        return back()->with('error', 'Only admins can delete users.');
    }

    if (!Hash::check($request->admin_password, $admin->password)) {
        return back()->with('error', 'Incorrect admin password.');
    }

    if ($admin->user_id == $id) {
        return back()->with('error', 'You cannot delete your own account.');
    }

    $user = User::findOrFail($id);

    // Delete all linked events first
    $user->events()->delete();

    // Then delete the user
    $user->delete();

    return redirect()->route('users.index')->with('success', 'Account deleted successfully.');
}



 public function uploadSignature(Request $request)
    {
        $request->validate([
            'signature' => 'required|image|mimes:jpg,jpeg,png,svg|max:3072', // 3MB
        ]);

        $user = auth()->user();

        // Delete old signature file if exists (optional)
        if ($user->signature) {
            Storage::disk('public')->delete($user->signature);
        }

        $path = $request->file('signature')->store('signatures', 'public');

        // Save path to users.signature
        $user->signature = $path;
        $user->save();

        // Also insert in signatures table for history (optional)
        try {
            \DB::table('signatures')->insert([
                'user_id' => $user->user_id,
                'signature_path' => $path,
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // ignore or log if already exists, but main user.signature is saved
        }

        return back()->with('success', 'Signature uploaded successfully!');
    }
    public function removeSignature(Request $request)
    {
        $user = auth()->user();

        if (!$user->signature) {
            return back()->with('error', 'No signature to remove.');
        }

        // delete file
        Storage::disk('public')->delete($user->signature);

        // remove path in users table
        $user->signature = null;
        $user->save();

        return back()->with('success', 'Signature removed.');
    }

public function updateContact(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:50',
            'contact_number' => 'required|string|max:20',
        ]);

        $user = auth()->user();
        $profile = $user->profile;

        if (! $profile) {
            return back()->with('error', 'Profile not found.');
        }

        $profile->update([
            'email' => $request->email,
            'contact_number' => $request->contact_number,
        ]);

        return back()->with('success', 'Contact details updated successfully!');
    }

}
