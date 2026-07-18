<?php

namespace App\Notifications;

use App\Models\CarWashProductSubscription;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Ativação do clube de lavagem aprovada pelo admin (task-5, seção 8).
 */
class ClubActivationApproved extends Notification
{
    public function __construct(public CarWashProductSubscription $subscription) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Clube de lavagem ativado — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("O clube de lavagem do \"{$this->subscription->carWash->name}\" foi aprovado e já está ativo.")
            ->line("Plano de repasse: {$this->subscription->payoutPlan->label}.")
            ->line('Seu lava-rápido já aparece para os assinantes e pode receber resgates de lavagem.');
    }
}
