<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\User;

class ImpersonateController extends Controller
{
    public function start($encrypted_id)
    {
        //  Security Check: Ensure only authorized admins can do this.
        if (!Auth::user()->can('user impersonate')) {
            abort(403, 'Unauthorized action. Only Super Admins can impersonate.');
        }

        // Prevent a user from impersonating themselves
        $targetId = decrypt($encrypted_id);
        if (Auth::id() == $targetId) {
            return back()->with('message_error', 'You cannot impersonate yourself.');
        }

        // Find the target user and store the original admin's ID in the session
        $targetUser = User::findOrFail($targetId);
        Session::put('impersonate_original_id', Auth::id());

        // Log in as the target user
        Auth::login($targetUser);

        // Redirect to dashboard
        return redirect('/')->with('message_success', "You are now impersonating {$targetUser->name}.");
    }

    public function leave()
    {
        // Check if we are actually impersonating someone
        if (!Session::has('impersonate_original_id')) {
            return redirect('/');
        }

        // Retrieve the original admin ID and remove it from the session
        $originalId = Session::pull('impersonate_original_id');

        // Log back in as the original admin
        Auth::loginUsingId($originalId);

        // Redirect back to the users list
        return redirect()->route('user.index')->with('message_success', 'Welcome back to your original account.');
    }
}
