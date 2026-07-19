<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\View\View;
use OwenIt\Auditing\Models\Audit;

/**
 * Consulta do log gerado pelo owen-it/laravel-auditing (task-3, seção 5;
 * task-11, seção 4) — só leitura, "quem fez o quê". Filtro por model e
 * período.
 */
class AuditController extends Controller
{
    /**
     * Lista atualizada dos Auditable definidos no projeto — fonte da
     * verdade é a task-3, seção 5 (conferir se divergir no futuro).
     *
     * @var array<int, class-string>
     */
    private const AUDITABLE_MODELS = [
        \App\Models\User::class,
        \App\Models\CarWash::class,
        \App\Models\CarWashProductSubscription::class,
        \App\Models\Plan::class,
        \App\Models\Subscription::class,
        \App\Models\WashRedemption::class,
        \App\Models\Payout::class,
        \App\Models\PayoutItem::class,
        \App\Models\PayoutPlan::class,
        \App\Models\PaymentGateway::class,
        \App\Models\Order::class,
    ];

    public function index(Request $request): View
    {
        $audits = Audit::query()
            ->with('user')
            ->when($request->query('model'), fn ($query, $model) => $query->where('auditable_type', $model))
            ->when($request->query('from'), fn ($query, $from) => $query->whereDate('created_at', '>=', $from))
            ->when($request->query('to'), fn ($query, $to) => $query->whereDate('created_at', '<=', $to))
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.audits.index', [
            'audits' => $audits,
            'models' => self::AUDITABLE_MODELS,
        ]);
    }
}
