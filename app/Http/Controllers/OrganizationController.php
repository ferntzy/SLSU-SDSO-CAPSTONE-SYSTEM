<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use Illuminate\Http\Request;


class OrganizationController extends Controller
{
    public function index()
    {
        $organizations = Organization::all();
        return view('admin.organizations.organizations', compact('organizations'));
    }
}
