<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CarWash;
use App\Models\CarWashInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation;
use App\Notifications\TeamMemberLinked;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Equipe do lava-rápido (task-5, seção 6): o owner convida por e-mail.
 * E-mail já cadastrado -> vincula direto como employee; inexistente ->
 * convite pendente em car_wash_invitations com token e validade.
 */
class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $carWash = $this->ownedCarWashOrAbort($request);

        return view('panel.team', [
            'carWash' => $carWash,
            'members' => $carWash->users()->get(),
            'pendingInvitations' => $carWash->invitations()
                ->whereNull('accepted_at')
                ->where('expires_at', '>', now())
                ->get(),
        ]);
    }

    public function invite(Request $request): RedirectResponse
    {
        $carWash = $this->ownedCarWashOrAbort($request);

        $validated = $request->validate([
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255'],
        ]);

        $existing = User::where('email', $validated['email'])->first();

        if ($existing !== null) {
            // Vincula direto (inclusive quem já é assinante do clube —
            // permitido e esperado, ver task-3 sobre role).
            if ($carWash->users()->whereKey($existing->id)->exists()) {
                return redirect()->route('panel.team.index')
                    ->with('error', 'Essa pessoa já faz parte da equipe.');
            }

            $carWash->users()->attach($existing->id, ['role' => 'employee']);
            $existing->notify(new TeamMemberLinked($carWash));

            return redirect()->route('panel.team.index')
                ->with('success', 'Pessoa vinculada à equipe e avisada por e-mail.');
        }

        $invitation = CarWashInvitation::create([
            'car_wash_id' => $carWash->id,
            'email' => $validated['email'],
            'token' => Str::random(48),
            'expires_at' => now()->addDays(7),
        ]);

        Notification::route('mail', $validated['email'])
            ->notify(new TeamInvitation($invitation));

        return redirect()->route('panel.team.index')
            ->with('success', 'Convite enviado por e-mail (válido por 7 dias).');
    }

    private function ownedCarWashOrAbort(Request $request): CarWash
    {
        $carWash = CarWash::findOrFail(session('current_car_wash_id'));

        // Um 'employee' não convida gente (task-14, seção 3).
        abort_unless(
            $carWash->users()->wherePivot('role', 'owner')->whereKey($request->user()->id)->exists(),
            403,
        );

        return $carWash;
    }
}
