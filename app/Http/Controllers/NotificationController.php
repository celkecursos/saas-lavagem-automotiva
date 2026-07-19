<?php

namespace App\Http\Controllers;

use App\Models\CarWash;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Sino de notificações — mesma tela/rotas pra qualquer usuário
 * autenticado (admin, lava-rápido, assinante), lendo só as próprias
 * notifications (task-19, seção 4). GET /notificacoes serve dois
 * formatos: JSON (polling do sino, últimas 5 + contador) e HTML
 * (lista completa paginada) — não duplica a query em duas rotas.
 */
class NotificationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $user = $request->user();

        if ($request->wantsJson()) {
            $items = $user->notifications()->latest()->take(5)->get()->map(fn ($notification) => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? '',
                'url' => $notification->data['url'] ?? route('notifications.index'),
                'time' => $notification->created_at->diffForHumans(),
            ]);

            return response()->json([
                'unread_count' => $user->unreadNotifications()->count(),
                'items' => $items,
            ]);
        }

        $notifications = $user->notifications()->latest()->paginate(15);
        $layout = $this->layoutFor($user);

        return view('notifications.index', compact('notifications', 'layout'));
    }

    public function markAsRead(Request $request, string $id): RedirectResponse|JsonResponse
    {
        $notification = $request->user()->notifications()->whereKey($id)->firstOrFail();
        $notification->markAsRead();

        if ($request->wantsJson()) {
            return response()->json(['status' => 'ok']);
        }

        return redirect()->route('notifications.index');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return redirect()->route('notifications.index')->with('success', 'Todas as notificações foram marcadas como lidas.');
    }

    /**
     * A mesma tela serve os 3 públicos (task-19, seção 4) — escolhe o
     * layout (e portanto a navegação/sidebar) pelo mesmo sinal que os
     * middlewares 'admin'/'car-wash' já usam: role='admin' pro staff,
     * vínculo em car_wash_users pro lava-rápido, público pro assinante.
     */
    private function layoutFor(User $user): string
    {
        if ($user->role === 'admin') {
            return 'admin';
        }

        if (CarWash::whereHas('users', fn ($query) => $query->whereKey($user->id))->exists()) {
            return 'car-wash-panel';
        }

        return 'public';
    }
}
