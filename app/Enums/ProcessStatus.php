<?php

namespace App\Enums;

enum ProcessStatus: string
{
    case Active = 'active';
    case Concluded = 'concluded';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Ativo (aluno em Regime de Ensino a Distância)',
            self::Concluded => 'Concluído (aluno retornou às aulas presenciais)',
        };
    }
}
