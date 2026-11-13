<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail enviado ao professor quando o aluno envia (ou reenvia) uma resolução.
 */
class ActivitySubmitted extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $activityTitle,
        public string $studentName,
        public string $processPublicId,
        public string $processUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Resolução de atividade enviada')
            ->greeting('Olá!')
            ->line('Um aluno enviou uma resolução para uma atividade sob sua correção.')
            ->line("Atividade: {$this->activityTitle}")
            ->line("Aluno: {$this->studentName}")
            ->line("Processo: {$this->processPublicId}")
            ->action('Corrigir atividade', $this->processUrl);
    }
}
