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
        'user_id.required' => 'Selecione o aluno.',
        'user_id.exists' => 'Aluno inválido.',
        'disciplines.required' => 'Selecione ao menos uma disciplina.',
        'professors.required' => 'Selecione os professores.',
        'label.required' => 'Informe o nome.',
        'process_id.required' => 'Selecione o processo.',
        'process_id.exists' => 'Processo inválido.',
        'discipline_id.required' => 'Selecione a disciplina.',
        'discipline_id.exists' => 'Disciplina inválida.',
        'title.required' => 'Informe o título da atividade.',
        'description.required' => 'Informe a descrição da atividade.',
        'deadline.required' => 'Informe o prazo de entrega.',
        'activity_file.required' => 'Selecione um arquivo PDF.',
        'activity_file.file' => 'O arquivo enviado é inválido.',
        'activity_file.mimes' => 'O arquivo deve ser um PDF.',
        'activity_file.max' => 'O arquivo não pode ser maior que 10 MB.',
        'grade.required' => 'Informe a nota.',
        'grade.numeric' => 'A nota deve ser um número.',
        'grade.between' => 'A nota deve estar entre 0 e 10.',
        'feedback.required' => 'Informe o feedback ao aluno.',
        'resolution_type.required' => 'Escolha como enviar a resolução.',
        'resolution_file.required_if' => 'Selecione um arquivo PDF.',
        'resolution_file.file' => 'O arquivo enviado é inválido.',
        'resolution_file.mimes' => 'O arquivo deve ser um PDF.',
        'resolution_file.max' => 'O arquivo não pode ser maior que 10 MB.',
        'resolution_link.required_if' => 'Informe o link da resolução.',
        'resolution_link.url' => 'Informe um link válido.',
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
            foreach (self::MESSAGES as $messageKey => $message) {
                if ($messageKey === $key || str_starts_with($messageKey, $key.'.')) {
                    $messages[$messageKey] = $message;
                }
            }
        }

        return $messages;
    }
}
