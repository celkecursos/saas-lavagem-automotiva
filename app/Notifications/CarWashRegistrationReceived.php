<?php

namespace App\Notifications;

use App\Models\CarWash;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Confirmação pós-cadastro do lava-rápido: recebido e aguardando
 * aprovação do admin (task-5, seção 8).
 */
class CarWashRegistrationReceived extends Notification
{
    public function __construct(public CarWash $carWash) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Cadastro recebido — Celke Wash Club')
            ->greeting("Olá, {$notifiable->name}!")
            ->line("Recebemos o cadastro do lava-rápido \"{$this->carWash->name}\".")
            ->line('Nossa equipe vai revisar os dados e você será avisado assim que o cadastro for aprovado.')
            ->line('Enquanto isso, confirme seu e-mail pelo link que enviamos em outra mensagem — cadastros com e-mail verificado são revisados primeiro.');
    }
}
