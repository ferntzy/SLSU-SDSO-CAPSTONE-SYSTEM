<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Vp_sasController extends Controller
{
    //
      public function profile()
    {
        // User must be logged in
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        return view('vp_sas.profile');
    }

}
