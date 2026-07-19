<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\LoyaltyPointsLedgerEntry;
use App\Models\LoyaltyRedemption;
use App\Services\Loyalty\LoyaltyRedemptionService;
use App\Services\Loyalty\LoyaltyRedemptionValidationException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Painel de fidelidade do assinante (task-20, seções 4 e 5).
 */
class LoyaltyController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $balance = LoyaltyPointsLedgerEntry::balanceFor($user->id);

        $unlockedIds = $user->userAchievements()->pluck('achievement_id');
        $unlocked = Achievement::whereIn('id', $unlockedIds)->where('active', true)->get();
        $locked = Achievement::whereNotIn('id', $unlockedIds)->where('active', true)->get();

        $ledger = $user->loyaltyLedgerEntries()->latest('created_at')->get();

        return view('subscriber.loyalty.index', compact('balance', 'unlocked', 'locked', 'ledger'));
    }

    public function shop(Request $request): View
    {
        $user = $request->user();
        $balance = LoyaltyPointsLedgerEntry::balanceFor($user->id);
        $redemptions = LoyaltyRedemption::where('active', true)->orderBy('points_cost')->get();

        return view('subscriber.loyalty.shop', compact('balance', 'redemptions'));
    }

    public function redeem(Request $request, LoyaltyRedemption $loyaltyRedemption, LoyaltyRedemptionService $service): RedirectResponse
    {
        try {
            $service->redeem($request->user(), $loyaltyRedemption);
        } catch (LoyaltyRedemptionValidationException $e) {
            return back()->with('error', $e->getMessage());
        }

        return redirect()->route('loyalty.shop')->with('success', 'Recompensa resgatada!');
    }
}
