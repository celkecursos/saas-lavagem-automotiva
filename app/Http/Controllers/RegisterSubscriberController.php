<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterSubscriberRequest;
use App\Models\ReferralReward;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Cadastro self-service do assinante (task-7, seção 1). A capacidade de
 * assinante vem de ter uma subscriptions, não do role (task-3, seção 1).
 */
class RegisterSubscriberController extends Controller
{
    public function create(Request $request): View
    {
        // GET /registro?ref={code} pré-preenche o campo (task-16, seção 4).
        return view('subscriber.register', ['referralCode' => $request->query('ref')]);
    }

    public function store(RegisterSubscriberRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'cpf' => $validated['cpf'] ?? null,
            'password' => $validated['password'],
        ]);

        $this->applyReferralCode($user, $validated['referral_code'] ?? null);

        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('plans.index');
    }

    /**
     * Código inválido (inexistente ou autoindicação) simplesmente não
     * vincula nada — nunca bloqueia o cadastro (task-16, seção 2).
     */
    private function applyReferralCode(User $user, ?string $referralCode): void
    {
        if (blank($referralCode)) {
            return;
        }

        $referrer = User::where('referral_code', Str::upper($referralCode))->first();

        if ($referrer === null || $referrer->id === $user->id) {
            return;
        }

        $user->update(['referred_by_user_id' => $referrer->id]);

        ReferralReward::create([
            'referrer_user_id' => $referrer->id,
            'referred_user_id' => $user->id,
            'status' => 'pending',
        ]);
    }
}
