<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\StaffInvitation;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AcceptInvitationController extends Controller
{
    public function show(Request $request)
    {
        $token = $request->query('token');
        $invitation = StaffInvitation::where('token', $token)
            ->pending()
            ->firstOrFail();

        return view('auth.accept-invitation', [
            'invitation' => $invitation,
            'token' => $token,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required|exists:staff_invitations,token',
            'name' => 'required|string|max:255',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $invitation = StaffInvitation::where('token', $request->token)
            ->pending()
            ->firstOrFail();

        if ($invitation->isExpired()) {
             return back()->withErrors(['token' => 'This invitation has expired.']);
        }

        // Create User
        $user = User::create([
            'name' => $request->name,
            'email' => $invitation->email,
            'password' => Hash::make($request->password),
            'role_id' => Role::where('slug', $invitation->role)->first()?->id,
            'status' => 'active',
            'invited_by' => $invitation->invited_by,
            'invited_at' => $invitation->created_at,
            'email_verified_at' => now(),
        ]);

        // Mark invitation accepted
        $invitation->markAsAccepted($user);

        // Login
        Auth::login($user);

        return redirect('/admin');
    }
}
