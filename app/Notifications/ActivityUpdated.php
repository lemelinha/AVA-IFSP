<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail enviado ao aluno quando o professor altera uma atividade já publicada.
 */
class ActivityUpdated extends Notification implements ShouldQueue
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
            ->subject('Atividade atualizada no seu processo RED')
            ->greeting('Olá!')
            ->line('O professor atualizou o prazo ou o conteúdo de uma atividade do seu Regime de Ensino a Distância.')
            ->line("Atividade: {$this->activityTitle}")
            ->line("Disciplina: {$this->disciplineLabel}")
            ->line("Novo prazo: {$this->deadline}")
            ->action('Ver atividade', $this->activityUrl);
    }
}
