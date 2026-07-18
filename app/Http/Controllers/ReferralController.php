<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * "Minhas indicações" no painel do assinante (task-16, seção 3).
 */
class ReferralController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $referrals = $user->referralsMade()
            ->with('referred')
            ->latest()
            ->get();

        return view('subscriber.referrals', [
            'referralCode' => $user->referral_code,
            'referrals' => $referrals,
            // Contador "você já ganhou X lavagens grátis por indicação".
            'grantedCount' => $referrals->where('status', 'granted')->count(),
        ]);
    }
}
