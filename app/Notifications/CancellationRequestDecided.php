<?php

namespace App\Notifications;

use App\Models\CancellationRequest;
use App\Models\ParkingSession;
use App\Models\WashRedemption;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Solicitação de cancelamento aprovada/rejeitada pelo admin (task-19,
 * seção 2) — avisa o dono/equipe do lava-rápido que abriu o pedido.
 */
class CancellationRequestDecided extends Notification
{
    public function __construct(public CancellationRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Solicitação de cancelamento '.$this->decisionLabel().' — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line('Sua solicitação de cancelamento foi '.$this->decisionLabel().'.')
            ->action('Ver detalhes', $this->url());
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Solicitação de cancelamento '.$this->decisionLabel(),
            'body' => $this->request->reason,
            'url' => $this->url(),
        ];
    }

    private function decisionLabel(): string
    {
        return $this->request->status === 'approved' ? 'aprovada' : 'rejeitada';
    }

    private function url(): string
    {
        return match ($this->request->requestable_type) {
            WashRedemption::class => route('panel.washes.index'),
            ParkingSession::class => route('panel.parking.sessions.index'),
            default => route('panel.dashboard'),
        };
    }
}
