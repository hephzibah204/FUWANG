<?php

namespace App\Http\Controllers;

use App\Models\EmailPreference;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class EmailPreferenceController extends Controller
{
    public function unsubscribe(Request $request)
    {
        $userId = (int) $request->route('user');
        $scope = (string) $request->route('scope');

        $user = User::query()->find($userId);
        if (!$user) {
            abort(404);
        }

        $updated = false;

        if (Schema::hasTable('email_preferences')) {
            $pref = EmailPreference::query()->firstOrCreate(
                ['user_id' => $user->id],
                ['welcome_enabled' => true, 'login_alerts_enabled' => true]
            );

            if ($scope === 'login') {
                $pref->login_alerts_enabled = false;
                $pref->save();
                $updated = true;
            } elseif ($scope === 'welcome') {
                $pref->welcome_enabled = false;
                $pref->save();
                $updated = true;
            } else {
                $pref->welcome_enabled = false;
                $pref->login_alerts_enabled = false;
                $pref->unsubscribed_at = now();
                $pref->save();
                $updated = true;
            }
        }

        return view('emails.unsubscribe', [
            'user' => $user,
            'scope' => $scope,
            'updated' => $updated,
        ]);
    }
}
