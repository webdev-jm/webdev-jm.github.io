<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use Auth;

class GoogleController extends Controller
{
    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            $user = User::where('email', $googleUser->email)->first();

            if (!$user) {
                $email_arr = explode('@', $googleUser->email);
                $password = Hash::make(reset($email_arr).'123!');

                $user = User::create([
                    'name' => $googleUser->name,
                    'email' => $googleUser->email,
                    'password' => $password, // Generate random password
                    'google_id' => $googleUser->id,
                ]);

                $user->assignRole('superadmin');
            }

            Auth::login($user);

            return redirect()->route('home'); // Redirect to dashboard
        } catch (\Exception $e) {
            return redirect('/login')->with('error', 'Something went wrong.');
        }
    }
}
