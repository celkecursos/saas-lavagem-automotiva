<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Assinatura cancelada — self-service ou automática por falha de
 * renovação após a carência (task-7, seções 4/5/7).
 */
class SubscriptionCanceled extends Notification
{
    public function __construct(public Subscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Assinatura cancelada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}.")
            ->line("Sua assinatura do plano \"{$this->subscription->plan->name}\" foi cancelada.");
    }
}
