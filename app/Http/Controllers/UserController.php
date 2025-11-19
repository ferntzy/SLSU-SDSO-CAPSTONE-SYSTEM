<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
 public function index(Request $request)
{
    // Allowed columns for sorting
    $sortable = ['account_role', 'created_at'];

    // Get query parameters
    $sortField = $request->query('sort');
    $sortDirection = $request->query('direction', 'asc');

    // Validate sorting
    if (!in_array($sortField, $sortable)) {
        $sortField = 'created_at'; // default sort
    }
    if (!in_array($sortDirection, ['asc', 'desc'])) {
        $sortDirection = 'asc';
    }

    // Fetch users with sorting
    $users = User::orderBy($sortField, $sortDirection)->get();

    // Pass sort info to the view (so arrows know which is active)
    return view('admin.users.index', compact('users', 'sortField', 'sortDirection'));
}



public function create()
{
    return view('admin.users.create');
}


  public function store(Request $request)
  {
    $request->validate([
      'username' => 'required|unique:users,username',
      'password' => 'required|min:6',
      'account_role' => 'required',
    ]);

    User::create([
      'username' => $request->username,
      'password' => Hash::make($request->password),
      'account_role' => $request->account_role,
    ]);

    return redirect()->route('users.index')->with('success', 'User created successfully!');
  }


  public function viewProfile($id)
{
    $user = User::with('profile')->findOrFail($id);

    return view('admin.users.partials.view-profile', compact('user'));
}






public function edit(User $user)
  {
    return view('admin.users.edit', compact('user'));
  }

public function update(Request $request, User $user)
{

    // dd($user);

    try {
        $request->validate([
            'username' => 'required|unique:users,username,' . $user->user_id . ',user_id',
            'account_role' => 'required|string',
            'password' => 'nullable|min:6',
        ]);

        $data = $request->only('username', 'account_role');

        if ($request->filled('password')) {
            $data['password'] = bcrypt($request->password);
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');

    } catch (\Illuminate\Validation\ValidationException $e) {
        return back()->withErrors($e->errors())->withInput();
    } catch (\Exception $e) {
        return back()->with('error', 'Something went wrong: ' . $e->getMessage())->withInput();
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






}
