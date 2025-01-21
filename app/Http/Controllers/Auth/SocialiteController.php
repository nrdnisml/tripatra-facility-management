<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserRole;
use Laravel\Socialite\Facades\Socialite;

class SocialiteController extends Controller
{
    public function redirect()
    {
        return Socialite::driver('azure')->redirect();
    }

    public function callback()
    {
        try {
            $user_ad = Socialite::driver('azure')->stateless()->user();
            $email = strtolower($user_ad->email);
            $this->dumpNonTripatra($email);
        } catch (\Exception $e) {
            return redirect()->back();
        }
        $user = User::where('email', $email)->first();
        // Jika Tidak ada user
        if (!$user) {
            // Create user baru
            $user = User::updateOrCreate(
                ['email' => $email],
                [
                    'name'  => $user_ad->name,
                    'ad_id'  => $user_ad->id,
                    'email' => $email,
                    'role' => 'user',
                ]
            );

            $user_id = $user->id;
            UserRole::create([
                'user_id' => $user_id,
                'role_name' => 'user',
                'module' => 'all',
            ]);
        }
        Auth()->login($user, true);

        // direct to list of project so user must choose one project before visiting the dashboard
        return redirect()->intended(route('filament.meeting-room.pages.dashboard'));
    }

    private function dumpNonTripatra(string $email)
    {
        if (strtolower(explode('@', $email)[1]) != 'tripatra.com') {
            return redirect(route('login'));
        }
    }
}
