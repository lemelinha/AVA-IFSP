<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * E-mail enviado ao professor quando um aluno é vinculado a uma de suas disciplinas.
 */
class StudentLinkedToDiscipline extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  array<int, string>  $disciplineLabels
     */
    public function __construct(
        public string $studentName,
        public array $disciplineLabels,
        public string $processPublicId,
        public string $processUrl,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $disciplines = implode(', ', $this->disciplineLabels);

        return (new MailMessage)
            ->subject('Novo aluno em RED na sua disciplina')
            ->greeting('Olá!')
            ->line('Um novo aluno em Regime de Ensino a Distância foi vinculado a uma disciplina sob sua responsabilidade.')
            ->line("Aluno: {$this->studentName}")
            ->line("Disciplina(s): {$disciplines}")
            ->line("Processo: {$this->processPublicId}")
            ->line('Disponibilize os conteúdos, materiais e atividades para que o aluno consiga acompanhar a matéria durante o afastamento.')
            ->action('Abrir processo', $this->processUrl);
    }
}
