<?php

namespace App\Notifications;

use App\Models\Achievement;
use Illuminate\Notifications\Notification;

/**
 * Conquista desbloqueada (task-20, seção 3) — só database, sem e-mail
 * (é um reforço motivacional no painel, não algo que precise chegar
 * na caixa de entrada).
 */
class AchievementUnlocked extends Notification
{
    public function __construct(public Achievement $achievement) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => "Conquista desbloqueada: {$this->achievement->name}",
            'body' => $this->achievement->description,
            'url' => route('loyalty.index'),
        ];
    }
}
