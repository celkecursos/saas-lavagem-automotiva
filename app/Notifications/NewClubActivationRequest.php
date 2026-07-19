<?php

namespace App\Notifications;

use App\Models\CarWashProductSubscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Pedido de ativação do clube de lavagem aguardando aprovação
 * (task-19, seção 2) — pros admins com a permission
 * car-wash-product-subscriptions.approve.
 */
class NewClubActivationRequest extends Notification
{
    public function __construct(public CarWashProductSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Novo pedido de ativação do clube de lavagem — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("\"{$this->subscription->carWash->name}\" solicitou a ativação do clube de lavagem.")
            ->action('Ver fila de ativação', route('car-wash-product-subscriptions.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Novo pedido de ativação do clube de lavagem',
            'body' => $this->subscription->carWash->name,
            'url' => route('car-wash-product-subscriptions.index'),
        ];
    }
}
