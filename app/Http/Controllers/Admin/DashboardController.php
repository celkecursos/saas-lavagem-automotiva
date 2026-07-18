<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/**
 * Dashboard do admin (task-14, seção 4 / task-11, seção 2): KPIs por
 * query direta, sem tabela de cache/agregação na v1.
 */
class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $kpis = [
            'active_subscribers' => DB::table('subscriptions')
                ->where('status', 'active')
                ->count(),
            // MRR aproximado: soma do preço do plano das assinaturas ativas.
            'mrr_cents' => (int) DB::table('subscriptions')
                ->join('plans', 'plans.id', '=', 'subscriptions.plan_id')
                ->where('subscriptions.status', 'active')
                ->sum('plans.price_cents'),
            'car_washes_approved' => DB::table('car_washes')
                ->where('status', 'approved')
                ->whereNull('deleted_at')
                ->count(),
            'car_washes_pending' => DB::table('car_washes')
                ->where('status', 'pending')
                ->whereNull('deleted_at')
                ->count(),
            'washes_this_month' => DB::table('wash_redemptions')
                ->where('status', 'completed')
                ->whereBetween('redeemed_at', [now()->startOfMonth(), now()->endOfMonth()])
                ->count(),
            'payouts_pending_count' => DB::table('payouts')
                ->where('status', 'pending')
                ->count(),
            'payouts_pending_cents' => (int) DB::table('payouts')
                ->where('status', 'pending')
                ->sum('total_amount_cents'),
        ];

        // Filas com pendência (atalhos com contador — task-14, seção 4).
        $queues = [
            'pending_registrations' => $kpis['car_washes_pending'],
            'pending_club_activations' => DB::table('car_wash_product_subscriptions')
                ->where('product', 'clube_lavagem')
                ->where('status', 'pending')
                ->count(),
            'pending_cancellations' => DB::table('cancellation_requests')
                ->where('status', 'pending')
                ->count(),
            'flagged_parking_charges' => DB::table('parking_billing_charges')
                ->where('flagged_for_review', true)
                ->count(),
        ];

        return view('admin.dashboard', compact('kpis', 'queues'));
    }
}
