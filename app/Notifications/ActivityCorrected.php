<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail enviado ao aluno quando o professor corrige uma atividade.
 */
class ActivityCorrected extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $activityTitle,
        public string $disciplineLabel,
        public string $grade,
        public string $activityUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Atividade corrigida no seu processo RED')
            ->greeting('Olá!')
            ->line('Uma atividade do seu Regime de Ensino a Distância foi corrigida pelo professor.')
            ->line("Atividade: {$this->activityTitle}")
            ->line("Disciplina: {$this->disciplineLabel}")
            ->line("Nota: {$this->grade}")
            ->action('Ver correção', $this->activityUrl);
    }
}
