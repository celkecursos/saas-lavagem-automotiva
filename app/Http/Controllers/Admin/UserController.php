<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LoyaltyPointsLedgerEntry;
use App\Models\User;
use App\Models\WashRedemption;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Visão central da PESSOA (task-22) — agrega tudo que já existe
 * espalhado pelas outras tasks, sem tabela nova além de
 * suspended_at/suspension_reason. Não edita e-mail/CPF/senha
 * diretamente (evita inconsistência com o que o próprio usuário
 * validou) — só suspender/reativar/reenviar verificação.
 */
class UserController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $role = $request->query('role');

        $users = User::query()
            ->when($search, fn ($query, $search) => $query->where(fn ($q) => $q
                ->where('name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('cpf', 'like', "%{$search}%")
                ->orWhere('phone', 'like', "%{$search}%")))
            ->when($role === 'subscriber', fn ($query) => $query->whereHas('subscriptions', fn ($q) => $q->where('status', 'active')))
            ->when($role === 'car-wash', fn ($query) => $query->whereHas('carWashes'))
            ->when($role === 'admin', fn ($query) => $query->where('role', 'admin'))
            ->when($role === 'suspended', fn ($query) => $query->whereNotNull('suspended_at'))
            ->withCount(['subscriptions' => fn ($q) => $q->where('status', 'active')])
            ->with('carWashes')
            ->latest('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users', 'search', 'role'));
    }

    public function show(User $user): View
    {
        $user->load([
            'subscriptions' => fn ($q) => $q->with('plan')->latest('created_at'),
            'orders' => fn ($q) => $q->latest('created_at'),
            'vehicles' => fn ($q) => $q->latest('created_at'),
            'carWashRatings' => fn ($q) => $q->with('carWash')->latest('created_at'),
            'referralsMade' => fn ($q) => $q->with('referred')->latest('created_at'),
            'referralReceived' => fn ($q) => $q->with('referrer')->latest('created_at'),
            'carWashes',
            'cancellationRequestsMade' => fn ($q) => $q->where('status', 'pending')->latest('created_at'),
            'userAchievements' => fn ($q) => $q->with('achievement')->latest('unlocked_at'),
        ]);

        $cycleIds = $user->subscriptions->flatMap(fn ($subscription) => $subscription->cycles()->pluck('id'));
        $washRedemptions = WashRedemption::whereIn('subscription_cycle_id', $cycleIds)
            ->with('carWash')
            ->latest('created_at')
            ->get();

        $loyaltyBalance = LoyaltyPointsLedgerEntry::balanceFor($user->id);

        return view('admin.users.show', compact('user', 'washRedemptions', 'loyaltyBalance'));
    }

    public function suspend(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'suspension_reason' => ['required', 'string', 'max:2000'],
        ]);

        $user->update([
            'suspended_at' => now(),
            'suspension_reason' => $validated['suspension_reason'],
        ]);

        return redirect()->route('users.show', $user)->with('success', 'Conta suspensa.');
    }

    public function reactivate(User $user): RedirectResponse
    {
        $user->update(['suspended_at' => null, 'suspension_reason' => null]);

        return redirect()->route('users.show', $user)->with('success', 'Conta reativada.');
    }

    public function resendVerification(User $user): RedirectResponse
    {
        if (! $user->hasVerifiedEmail()) {
            event(new Registered($user));
        }

        return redirect()->route('users.show', $user)->with('success', 'E-mail de verificação reenviado.');
    }
}
