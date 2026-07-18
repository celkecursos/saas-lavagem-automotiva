<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Troca de plano agendada pro próximo ciclo (task-7, seções 5 e 7).
 */
class PlanChangeScheduled extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Troca de plano agendada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Sua troca pro plano \"{$this->subscription->pendingPlan->name}\" foi agendada.")
            ->line("Ela entra em vigor na sua próxima renovação, em {$this->subscription->current_period_end->format('d/m/Y')}.");
    }
}
