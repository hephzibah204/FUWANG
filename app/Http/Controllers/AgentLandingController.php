<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AgentLandingController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $agent = $user ? $user->enrollmentAgent : null;

        return view('agent.landing', compact('agent'));
    }
}
