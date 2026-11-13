<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail enviado ao aluno quando o professor publica uma atividade.
 */
class ActivityPublished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $activityTitle,
        public string $disciplineLabel,
        public string $deadline,
        public string $activityUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Nova atividade publicada no seu processo RED')
            ->greeting('Olá!')
            ->line('Uma nova atividade foi publicada para você no Regime de Ensino a Distância.')
            ->line("Atividade: {$this->activityTitle}")
            ->line("Disciplina: {$this->disciplineLabel}")
            ->line("Prazo de entrega: {$this->deadline}")
            ->action('Ver atividade', $this->activityUrl);
    }
}
