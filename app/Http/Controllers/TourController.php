<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TourController extends Controller
{
    public function complete(Request $request)
    {
        $user = Auth::user();
        $tour = $request->input('tour');

        if ($user && $tour) {
            $completed_tours = $user->completed_tours ?? [];
            if (!in_array($tour, $completed_tours)) {
                $completed_tours[] = $tour;
                $user->completed_tours = $completed_tours;
                $user->save();
            }
        }

        return response()->json(['status' => 'success']);
    }
}
