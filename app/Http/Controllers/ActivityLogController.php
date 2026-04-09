<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function index()
    {
        $activities = Activity::where('causer_id', Auth::id())
            ->latest()
            ->paginate(20);

        return view('profile.activity', compact('activities'));
    }
}
