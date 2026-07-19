<?php

namespace App\Notifications;

use App\Models\CancellationRequest;
use App\Models\ParkingSession;
use App\Models\WashRedemption;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Solicitação de cancelamento aberta (lavagem ou sessão de
 * estacionamento — task-19, seção 2) — pros admins com a permission
 * cancellation-requests.approve.
 */
class NewCancellationRequest extends Notification
{
    public function __construct(public CancellationRequest $request) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova solicitação de cancelamento — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line($this->description())
            ->action('Ver solicitações', route('cancellation-requests.index'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'title' => 'Nova solicitação de cancelamento',
            'body' => $this->description(),
            'url' => route('cancellation-requests.index'),
        ];
    }

    private function description(): string
    {
        return match ($this->request->requestable_type) {
            WashRedemption::class => 'Cancelamento de lavagem solicitado.',
            ParkingSession::class => 'Cancelamento de sessão de estacionamento solicitado.',
            default => 'Nova solicitação de cancelamento.',
        };
    }
}
