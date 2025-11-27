<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\UserProfile;
use App\Models\UserLog;
use Exception;
use Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{

  public function profile()
    {
        // User must be logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('admin.users.profile');
    }




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
                      ->orWhere('email', 'LIKE', "%{$query}%")
                      ->orWhere('type', 'LIKE', "%{$query}%");
                });
          });
      }

      $user_accounts = $user_accounts->get();

      // Get profile IDs already assigned to users
      $assigned_profiles = User::pluck('profile_id')->toArray();

      // Only show unassigned student profiles
      $user_profiles_student = UserProfile::where('type','student')
          ->whereNotIn('profile_id', $assigned_profiles)
          ->orderBy('last_name')
          ->orderBy('first_name')
          ->get();

      // Only show unassigned employee profiles
      $user_profiles_employee = UserProfile::where('type','employee')
          ->whereNotIn('profile_id', $assigned_profiles)
          ->orderBy('last_name')
          ->orderBy('first_name')
          ->get();

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



public function update(Request $request, User $user)
{
    try {
        $id = Crypt::decryptString($request->hiddenAccountID);

        $validated = $request->validate([
            'username'      => 'required|string|max:255',
            'account_role'  => 'required|string|max:255',
            'password'      => 'nullable|min:6',
        ]);

        // Fetch the user
        $user_account = User::where('user_id', $id)->firstOrFail();

        // Store old values for logging
        $oldUsername = $user_account->username;
        $oldRole     = $user_account->account_role;

        // Update username and account_role
        $user_account->username = $validated['username'];
        $user_account->account_role = $validated['account_role'];

        // Update password only if user typed a new one
        if (!empty($validated['password'])) {
            $user_account->password = bcrypt($validated['password']);
        }

        $user_account->save();

        // ===================== LOG ACTION =====================
        UserLog::create([
            'user_id'    => auth()->id(), // logged-in user who made the change
            'username'   => $user_account->username, // the account being edited
            'action'     => "Updated Account (Username: $oldUsername, Role: $oldRole → New Username: {$validated['username']}, New Role: {$validated['account_role']})",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return response()->json(['success' => 'User updated successfully.']);

    } catch (Exception $e) {
        return response()->json([
            'errors' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>'
        ], 400);
    }
}




 public function edit(Request $request)
{
    try {
        $id = Crypt::decryptString($request->id);

        // =========================================================
        // GET USER + PROFILE
        // =========================================================
        $user_account = User::with('profile')->findOrFail($id);

        // Profiles assigned to OTHER users only
        $assigned_profiles = User::where('user_id', '!=', $id)
            ->pluck('profile_id')
            ->toArray();

        // STUDENT LIST (unassigned)
        $student_profiles = UserProfile::where('type', 'student')
            ->whereNotIn('profile_id', $assigned_profiles)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // EMPLOYEE LIST (unassigned)
        $employee_profiles = UserProfile::where('type', 'employee')
            ->whereNotIn('profile_id', $assigned_profiles)
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        // =========================================================
        // FIX: ADD CURRENT PROFILE TO DROPDOWN EVEN IF ASSIGNED
        // =========================================================

        if ($user_account->profile->type === 'student') {
            if (!$student_profiles->contains('profile_id', $user_account->profile_id)) {
                $student_profiles->push($user_account->profile);
            }
        } else {
            if (!$employee_profiles->contains('profile_id', $user_account->profile_id)) {
                $employee_profiles->push($user_account->profile);
            }
        }

        return response()->json([
            'user_id'      => $user_account->user_id,
            'username'     => $user_account->username,
            'account_role' => $user_account->account_role,
            'profile_id'   => $user_account->profile_id,
            'profile'      => $user_account->profile,

            // Updated dropdown lists
            'student_profiles'  => $student_profiles->sortBy('last_name')->values(),
            'employee_profiles' => $employee_profiles->sortBy('last_name')->values(),
        ]);

    } catch (Exception $e) {
        return response()->json([
            'errors' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>'
        ], 400);
    }
}



  public function checkPassword(Request $request)
{
    $user = User::findOrFail($request->user_id);

    $match = Hash::check($request->password, $user->password);

    return response()->json(['match' => $match]);
}


 public function view(Request $request)
{
    try {
        $id = Crypt::decryptString($request->id);

        // load user + profile
        $user_account = User::with('profile')->findOrFail($id);

        return response()->json($user_account);

    } catch (Exception $e) {
        return response()->json([
            'errors' => '<div class="alert alert-danger">'.$e->getMessage().'</div>'
        ]);
    }
}


public function destroy($user_id)
{
    try {
        $user_id = Crypt::decryptString(urldecode($user_id));
        $user_account = User::findOrFail($user_id);

        // Log the deletion before deleting
        \App\Models\UserLog::create([
            'user_id'    => $user_account->user_id,
            'username'   => $user_account->username ?? 'Unknown',
            'action'     => 'Deleted Account',
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Delete the user
        $user_account->delete();

        return response()->json(['success' => true, 'id' => $user_id]);
    } catch (Exception $e) {
        return response()->json([
            'errors' => '<div class="alert alert-danger">' . $e->getMessage() . '</div>'
        ], 400);
    }
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
