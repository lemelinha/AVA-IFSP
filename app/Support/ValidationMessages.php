<?php

namespace App\Support;

/**
 * Centralizes the PT-BR validation messages used by the controllers.
 *
 * Usage: $request->validate($rules, ValidationMessages::for('name', 'email'));
 */
class ValidationMessages
{
    public const MESSAGES = [
        'name.required' => 'Informe o nome completo.',
        'prontuario.required' => 'Informe o prontuário.',
        'prontuario.unique' => 'Este prontuário já está em uso.',
        'email.required' => 'Informe o e-mail.',
        'email.email' => 'Informe um e-mail válido.',
        'email.unique' => 'Este e-mail já está em uso.',
        'password.required' => 'Informe a senha.',
        'password.min' => 'A senha precisa ter pelo menos :min caracteres.',
        'roles.*.exists' => 'Perfil de acesso inválido.',
        'course_code.required' => 'Informe o código do curso.',
        'course_code.unique' => 'Este código de curso já está em uso.',
        'class_code.required' => 'Informe o código da disciplina.',
        'class_code.unique' => 'Este código de disciplina já está em uso.',
        'course_id.required' => 'Selecione o curso.',
        'course_id.exists' => 'Curso inválido.',
        'label.required' => 'Informe o nome.',
    ];

    /**
     * Returns the messages for the given keys.
     *
     * @return array<string, string>
     */
    public static function for(string ...$keys): array
    {
        $messages = [];

        foreach ($keys as $key) {
            if (isset(self::MESSAGES[$key])) {
                $messages[$key] = self::MESSAGES[$key];
            }
        }

        return $messages;
    }
}
