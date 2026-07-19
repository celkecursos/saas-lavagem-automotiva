<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Chargeback recebido via webhook (task-21, seção 3; adicionada à
 * lista da task-19 depois de escrita, conforme já previsto lá) — mesma
 * permission de orders.refund, já que costuma exigir revisão humana
 * mesmo com a revogação automática já tendo acontecido.
 */
class ChargebackReceived extends Notification
{
    public function __construct(public Order $order) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->order->amount_cents / 100, 2, ',', '.');

        return (new MailMessage)
            ->subject('Chargeback recebido — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O pedido #{$this->order->id} (R$ {$amount}) sofreu chargeback pelo banco emissor do cartão.")
            ->line('O acesso do assinante já foi revogado automaticamente.')
            ->action('Ver pedido', route('orders.show', $this->order));
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->order->amount_cents / 100, 2, ',', '.');

        return [
            'title' => 'Chargeback recebido',
            'body' => "Pedido #{$this->order->id} — R$ {$amount}",
            'url' => route('orders.show', $this->order),
        ];
    }
}
