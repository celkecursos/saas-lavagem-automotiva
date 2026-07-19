<?php

namespace App\Notifications;

use App\Models\CarWashProductSubscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Ativação do clube de lavagem rejeitada pelo admin (task-5, seção 8).
 */
class ClubActivationRejected extends Notification
{
    public function __construct(public CarWashProductSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Ativação do clube de lavagem não aprovada — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}.")
            ->line("A solicitação de ativação do clube de lavagem do \"{$this->subscription->carWash->name}\" não foi aprovada.")
            ->line('Você pode fazer uma nova solicitação pelo painel, em Meus produtos.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Ativação do clube de lavagem não aprovada',
            'body' => $this->subscription->carWash->name,
            'url' => route('panel.products.index'),
        ];
    }
}
