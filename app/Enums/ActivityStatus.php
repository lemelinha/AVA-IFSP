<?php

namespace App\Enums;

enum ActivityStatus: string
{
    case Pending = 'pending';
    case Sent = 'sent';
    case InCorrection = 'in_correction';
    case Corrected = 'corrected';
    case Concluded = 'concluded';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pendente (aguardando envio pelo professor)',
            self::Sent => 'Enviada (aguardando resposta do aluno)',
            self::InCorrection => 'Em correção (aguardando correção pelo professor)',
            self::Corrected => 'Corrigida (corrigida pelo professor)',
            self::Concluded => 'Concluída (concluída pelo coordenador)',
        };
    }
}
