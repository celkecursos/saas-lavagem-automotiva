<?php

namespace App\Http\Controllers;

use App\Models\CarWashInvitation;
use App\Models\User;
use App\Notifications\TeamInviteAccepted;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Aceite público de convite de equipe (task-5, seção 6): cria o user
 * (role='user') e o vínculo employee; convite expirado não vale mais.
 */
class InvitationController extends Controller
{
    public function show(string $token): View
    {
        $invitation = CarWashInvitation::where('token', $token)->firstOrFail();

        if ($invitation->accepted_at !== null || $invitation->expires_at->isPast()) {
            return view('invitations.expired', compact('invitation'));
        }

        return view('invitations.accept', compact('invitation'));
    }

    public function accept(Request $request, string $token): RedirectResponse
    {
        $invitation = CarWashInvitation::where('token', $token)->firstOrFail();

        abort_if($invitation->accepted_at !== null, 403);
        abort_if($invitation->expires_at->isPast(), 403);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user = DB::transaction(function () use ($invitation, $validated) {
            // Se alguém criou a conta com este e-mail depois do convite,
            // só vincula — não duplica user.
            $user = User::firstOrCreate(
                ['email' => $invitation->email],
                ['name' => $validated['name'], 'password' => $validated['password']],
            );

            $invitation->carWash->users()->syncWithoutDetaching([
                $user->id => ['role' => 'employee'],
            ]);

            $invitation->update(['accepted_at' => now()]);

            return $user;
        });

        $owners = $invitation->carWash->users()->wherePivot('role', 'owner')->get();
        Notification::send($owners, new TeamInviteAccepted($invitation));

        Auth::login($user);

        return redirect()->route('panel.dashboard')
            ->with('success', "Bem-vindo à equipe do {$invitation->carWash->name}!");
    }
}
