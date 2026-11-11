@extends('layouts.app')

@section('title', 'Painel — Sistema RED')

@php
    $user = auth()->user()->loadMissing('roles');

    $roleNames = $user->roles->pluck('name')->all();

    $view = in_array('cti', $roleNames, true)
        ? 'cti'
        : (in_array('coordinator', $roleNames, true)
            ? 'coordinator'
            : (in_array('student', $roleNames, true) ? 'student' : 'professor'));

    $headers = [
        'cti' => ['Processos RED', 'Visão geral de todos os cursos do campus · 2026/1'],
        'coordinator' => ['Processos RED', 'Todos os processos do curso que você coordena · 2026/1'],
        'professor' => ['Atividades vinculadas', 'Processos nos quais você foi vinculado · 2026/1'],
        'student' => ['Seu processo RED', 'Acompanhamento das atividades da sua solicitação · 2026/1'],
    ];
    $titulo = $headers[$view][0];
    $subtitulo = $headers[$view][1];

    $counters = [
        'cti' => [
            ['n' => 28, 'l' => 'Processos no campus', 'warn' => false],
            ['n' => 61, 'l' => 'Usuários cadastrados', 'warn' => false],
            ['n' => 3, 'l' => 'Coordenadores', 'warn' => false],
            ['n' => 16, 'l' => 'Professores', 'warn' => false],
        ],
        'coordinator' => [
            ['n' => 12, 'l' => 'Processos abertos', 'warn' => false],
            ['n' => 5, 'l' => 'Aguardando validação', 'warn' => true],
            ['n' => 2, 'l' => 'Prontos para finalizar', 'warn' => true],
            ['n' => 41, 'l' => 'Validadas no mês', 'warn' => false],
        ],
        'professor' => [
            ['n' => 4, 'l' => 'Disciplinas vinculadas', 'warn' => false],
            ['n' => 6, 'l' => 'Resoluções recebidas', 'warn' => false],
            ['n' => 2, 'l' => 'Aguardando validação', 'warn' => true],
            ['n' => 18, 'l' => 'Validadas no mês', 'warn' => false],
        ],
        'student' => [
            ['n' => 3, 'l' => 'Atividades no processo', 'warn' => false],
            ['n' => 1, 'l' => 'Aguardando seu envio', 'warn' => true],
            ['n' => 1, 'l' => 'Enviada', 'warn' => false],
            ['n' => 1, 'l' => 'Devolvida', 'warn' => true],
        ],
    ];

    $columnsByView = [
        'cti' => ['aluno', 'curso', 'disciplinas', 'professor', 'status', 'acao'],
        'coordinator' => ['aluno', 'curso', 'disciplinas', 'professor', 'status', 'acao'],
        'professor' => ['aluno', 'disciplina', 'atividade', 'status', 'acao'],
        'student' => ['disciplina', 'professor', 'atividade', 'status', 'acao'],
    ];
    $colunas = $columnsByView[$view];

    $columnLabels = [
        'aluno' => 'Aluno',
        'curso' => 'Curso · Período',
        'disciplinas' => 'Disciplinas',
        'professor' => 'Professor',
        'disciplina' => 'Disciplina',
        'atividade' => 'Atividade',
        'status' => 'Status',
        'acao' => 'Ação',
    ];

    $statusStyles = [
        'Pendente' => 'border-[#f0dcb0] bg-[#fdf3e0] text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]',
        'Enviada' => 'border-[#c8dcf4] bg-[#e8f0fb] text-[#1d4f91] dark:border-[#9ec5f0]/30 dark:bg-[#e8f0fb]/10 dark:text-[#9ec5f0]',
        'Devolvida' => 'border-[#f3c9c9] bg-[#fdeaea] text-[#a4262c] dark:border-[#f2a3a5]/30 dark:bg-[#fdeaea]/10 dark:text-[#f2a3a5]',
        'Validada' => 'border-[#c4e2cc] bg-[#e7f4ea] text-[#1e6b34] dark:border-[#8fd0a3]/30 dark:bg-[#e7f4ea]/10 dark:text-[#8fd0a3]',
        'Finalizado' => 'border-[#172019] bg-[#172019] text-white dark:border-white dark:bg-white dark:text-[#172019]',
    ];

    $processos = [
        'cti' => [
            ['nome' => 'João M. Ferreira', 'prontuario' => 'JC001042', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 4, 'professor' => 'Prof. Carlos Lima', 'status' => 'Enviada', 'acao' => 'Abrir processo'],
            ['nome' => 'Maria C. Oliveira', 'prontuario' => 'JC001043', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 3, 'professor' => 'Prof. Ana Souza', 'status' => 'Pendente', 'acao' => 'Abrir processo'],
            ['nome' => 'Pedro A. Santos', 'prontuario' => 'JC001044', 'curso' => 'Eletrotécnica', 'periodo' => '2026/1', 'disciplinas' => 5, 'professor' => 'Prof. Rita Alves', 'status' => 'Devolvida', 'acao' => 'Abrir processo'],
            ['nome' => 'Larissa P. Ramos', 'prontuario' => 'JC001045', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 4, 'professor' => 'Prof. Carlos Lima', 'status' => 'Validada', 'acao' => 'Abrir processo'],
            ['nome' => 'Rafael T. Nunes', 'prontuario' => 'JC001046', 'curso' => 'Mecânica', 'periodo' => '2025/2', 'disciplinas' => 3, 'professor' => 'Prof. Bruno Dias', 'status' => 'Finalizado', 'acao' => 'Ver ata'],
            ['nome' => 'Camila R. Dias', 'prontuario' => 'JC001047', 'curso' => 'Eletrotécnica', 'periodo' => '2026/1', 'disciplinas' => 2, 'professor' => 'Prof. Rita Alves', 'status' => 'Pendente', 'acao' => 'Abrir processo'],
            ['nome' => 'Diego F. Martins', 'prontuario' => 'JC001048', 'curso' => 'Mecânica', 'periodo' => '2026/1', 'disciplinas' => 4, 'professor' => 'Prof. Bruno Dias', 'status' => 'Pendente', 'acao' => 'Abrir processo'],
            ['nome' => 'Beatriz L. Castro', 'prontuario' => 'JC001049', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 5, 'professor' => 'Prof. Ana Souza', 'status' => 'Enviada', 'acao' => 'Abrir processo'],
        ],
        'coordinator' => [
            ['nome' => 'João M. Ferreira', 'prontuario' => 'JC001042', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 4, 'professor' => 'Prof. Carlos Lima', 'status' => 'Enviada', 'acao' => 'Abrir processo'],
            ['nome' => 'Maria C. Oliveira', 'prontuario' => 'JC001043', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 3, 'professor' => 'Prof. Ana Souza', 'status' => 'Pendente', 'acao' => 'Abrir processo'],
            ['nome' => 'Pedro A. Santos', 'prontuario' => 'JC001044', 'curso' => 'Eletrotécnica', 'periodo' => '2026/1', 'disciplinas' => 5, 'professor' => 'Prof. Rita Alves', 'status' => 'Devolvida', 'acao' => 'Abrir processo'],
            ['nome' => 'Larissa P. Ramos', 'prontuario' => 'JC001045', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplinas' => 4, 'professor' => 'Prof. Carlos Lima', 'status' => 'Validada', 'acao' => 'Abrir processo'],
            ['nome' => 'Rafael T. Nunes', 'prontuario' => 'JC001046', 'curso' => 'Mecânica', 'periodo' => '2025/2', 'disciplinas' => 3, 'professor' => 'Prof. Bruno Dias', 'status' => 'Finalizado', 'acao' => 'Ver ata'],
            ['nome' => 'Camila R. Dias', 'prontuario' => 'JC001047', 'curso' => 'Eletrotécnica', 'periodo' => '2026/1', 'disciplinas' => 2, 'professor' => 'Prof. Rita Alves', 'status' => 'Pendente', 'acao' => 'Abrir processo'],
        ],
        'professor' => [
            ['nome' => 'João M. Ferreira', 'prontuario' => 'JC001042', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Matemática', 'atividade' => '2ª atividade', 'status' => 'Enviada', 'acao' => 'Ver resoluções'],
            ['nome' => 'Larissa P. Ramos', 'prontuario' => 'JC001045', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Matemática', 'atividade' => '1ª atividade', 'status' => 'Validada', 'acao' => 'Ver resoluções'],
            ['nome' => 'Maria C. Oliveira', 'prontuario' => 'JC001043', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Matemática', 'atividade' => '1ª atividade', 'status' => 'Pendente', 'acao' => 'Ver atividade'],
            ['nome' => 'Pedro A. Santos', 'prontuario' => 'JC001044', 'curso' => 'Eletrotécnica', 'periodo' => '2026/1', 'disciplina' => 'Matemática', 'atividade' => '3ª atividade', 'status' => 'Devolvida', 'acao' => 'Ver resoluções'],
        ],
        'student' => [
            ['nome' => 'Ana Beatriz Souza', 'prontuario' => 'JC000005', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Matemática', 'professor' => 'Prof. Carlos Lima', 'atividade' => '2ª atividade', 'status' => 'Enviada', 'acao' => 'Ver resolução'],
            ['nome' => 'Ana Beatriz Souza', 'prontuario' => 'JC000005', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Programação de Sistemas', 'professor' => 'Prof. Ana Souza', 'atividade' => '1ª atividade', 'status' => 'Pendente', 'acao' => 'Enviar resolução'],
            ['nome' => 'Ana Beatriz Souza', 'prontuario' => 'JC000005', 'curso' => 'Técnico em Informática', 'periodo' => '2026/1', 'disciplina' => 'Eletrônica Analógica', 'professor' => 'Prof. Rita Alves', 'atividade' => '1ª atividade', 'status' => 'Devolvida', 'acao' => 'Nova resolução'],
        ],
    ];

    $rows = $processos[$view];

    $metaLabels = [
        'curso' => 'Curso',
        'disciplinas' => 'Disciplinas',
        'professor' => 'Professor',
        'disciplina' => 'Disciplina',
        'atividade' => 'Atividade',
    ];
@endphp

@section('content')
    <div class="flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-[28px] font-semibold tracking-tight text-[#172019] sm:text-3xl dark:text-white">{{ $titulo }}</h1>
            <p class="mt-1.5 text-sm leading-6 text-[#647168] dark:text-white/65">{{ $subtitulo }}</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @role('coordinator')
                <button type="button" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                    Novo processo
                </button>
            @endrole

            @role('cti')
                <button type="button" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                    Cadastrar usuário
                </button>
            @endrole

            @role('student')
                <button type="button" class="h-11 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a] dark:bg-[#0f7a48] dark:hover:bg-[#0c6a3f]">
                    Enviar resolução
                </button>
            @endrole

            @hasanyrole(['cti', 'coordinator'])
                <button type="button" class="h-11 rounded-lg border border-[#006437]/30 bg-white px-5 text-sm font-semibold text-[#006437] transition hover:border-[#006437] hover:bg-[#006437]/5 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/15 dark:border-[#73c69b]/30 dark:bg-white/5 dark:text-[#73c69b] dark:hover:border-[#73c69b] dark:hover:bg-[#73c69b]/10">
                    Cadastrar aluno
                </button>
            @endhasanyrole
        </div>
    </div>

    <section aria-label="Resumo do período" class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
        <div class="grid grid-cols-2 lg:grid-cols-4">
            @foreach ($counters[$view] as $counter)
                <div class="border-r border-b border-[#eef2ee] p-5 [&:nth-child(2n)]:border-r-0 [&:nth-child(n+3)]:border-b-0 lg:border-b-0 lg:border-r lg:[&:nth-child(2n)]:border-r lg:last:border-r-0 dark:border-white/10">
                    <div class="text-[26px] font-semibold tracking-tight {{ $counter['warn'] ? 'text-[#d12a2e]' : 'text-[#006437]' }} dark:{{ $counter['warn'] ? 'text-[#f2a3a5]' : 'text-[#73c69b]' }}">
                        {{ $counter['n'] }}
                    </div>
                    <div class="mt-1 text-xs leading-5 text-[#647168] dark:text-white/60">{{ $counter['l'] }}</div>
                </div>
            @endforeach
        </div>
    </section>

    <section aria-label="Filtros" class="mt-5 rounded-2xl border border-[#d9e1d9] bg-white p-4 dark:border-white/10 dark:bg-[#102319]">
        <div class="flex flex-col gap-3 sm:flex-row sm:items-center">
            <div class="relative flex-1">
                <label for="f-busca" class="sr-only">Buscar processo</label>
                <svg class="pointer-events-none absolute top-1/2 left-3.5 h-4 w-4 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <circle cx="11" cy="11" r="7" />
                    <path stroke-linecap="round" d="M20 20l-3.6-3.6" />
                </svg>
                <input
                    id="f-busca"
                    type="search"
                    placeholder="Buscar por aluno, prontuário ou disciplina"
                    autocomplete="off"
                    class="h-11 w-full rounded-lg border border-[#d9e1d9] bg-white pr-4 pl-10 text-sm text-[#172019] placeholder:text-[#647168] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                >
            </div>

            <div class="relative">
                <label for="f-curso" class="sr-only">Filtrar por curso</label>
                <select id="f-curso" class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                    <option value="">Todos os cursos</option>
                    <option value="Técnico em Informática">Técnico em Informática</option>
                    <option value="Eletrotécnica">Eletrotécnica</option>
                    <option value="Mecânica">Mecânica</option>
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 9l6 6 6-6" />
                </svg>
            </div>

            <div class="relative">
                <label for="f-status" class="sr-only">Filtrar por status</label>
                <select id="f-status" class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                    <option value="">Todos os status</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Enviada">Enviada</option>
                    <option value="Devolvida">Devolvida</option>
                    <option value="Validada">Validada</option>
                    <option value="Finalizado">Finalizado</option>
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 9l6 6 6-6" />
                </svg>
            </div>

            <div class="relative">
                <label for="f-periodo" class="sr-only">Filtrar por período</label>
                <select id="f-periodo" class="h-11 w-full appearance-none rounded-lg border border-[#d9e1d9] bg-white px-3.5 pr-9 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 sm:w-auto dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                    <option value="">Todos os períodos</option>
                    <option value="2026/1">2026/1</option>
                    <option value="2025/2">2025/2</option>
                </select>
                <svg class="pointer-events-none absolute top-1/2 right-3.5 h-3.5 w-3.5 -translate-y-1/2 text-[#647168] dark:text-white/45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" d="M6 9l6 6 6-6" />
                </svg>
            </div>
        </div>
    </section>

    <div class="mt-5 hidden overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white md:block dark:border-white/10 dark:bg-[#102319]">
        <table class="w-full border-collapse text-left">
            <caption class="sr-only">{{ $titulo }} — dados de demonstração</caption>
            <thead>
                <tr class="border-b border-[#eef2ee] bg-[#f7faf7] dark:border-white/10 dark:bg-white/[0.03]">
                    @foreach ($colunas as $col)
                        <th scope="col" class="px-5 py-3 text-[11px] font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55 @if ($col === 'acao') text-right @endif">
                            {{ $columnLabels[$col] }}
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php
                        $busca = strtolower(trim(implode(' ', array_filter([
                            $row['nome'] ?? '',
                            $row['prontuario'] ?? '',
                            $row['disciplina'] ?? '',
                            $row['atividade'] ?? '',
                        ]))));
                    @endphp
                    <tr
                        data-fila
                        data-busca="{{ $busca }}"
                        data-curso="{{ $row['curso'] }}"
                        data-periodo="{{ $row['periodo'] }}"
                        data-status="{{ $row['status'] }}"
                        class="border-b border-[#eef2ee] transition last:border-b-0 hover:bg-[#f7faf7] dark:border-white/10 dark:hover:bg-white/[0.04]"
                    >
                        @foreach ($colunas as $col)
                            @if ($col === 'aluno')
                                <td class="px-5 py-4 align-middle text-sm">
                                    <span class="font-semibold text-[#172019] dark:text-white">{{ $row['nome'] }}</span>
                                    <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/55">Prontuário {{ $row['prontuario'] }}</span>
                                </td>
                            @elseif ($col === 'curso')
                                <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $row['curso'] }} · {{ $row['periodo'] }}</td>
                            @elseif ($col === 'disciplinas')
                                <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $row['disciplinas'] }} vinculadas</td>
                            @elseif ($col === 'professor')
                                <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $row['professor'] }}</td>
                            @elseif ($col === 'disciplina')
                                <td class="px-5 py-4 align-middle text-sm font-medium text-[#172019] dark:text-white">{{ $row['disciplina'] }}</td>
                            @elseif ($col === 'atividade')
                                <td class="px-5 py-4 align-middle text-sm text-[#647168] dark:text-white/65">{{ $row['atividade'] }}</td>
                            @elseif ($col === 'status')
                                <td class="px-5 py-4 align-middle">
                                    <span class="inline-flex rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusStyles[$row['status']] }}">{{ $row['status'] }}</span>
                                </td>
                            @elseif ($col === 'acao')
                                <td class="px-5 py-4 text-right align-middle">
                                    <button type="button" class="text-xs font-semibold text-[#006437] transition hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:text-[#73c69b]">
                                        {{ $row['acao'] }} →
                                    </button>
                                </td>
                            @endif
                        @endforeach
                    </tr>
                @endforeach
                <tr class="js-vazio hidden">
                    <td colspan="{{ count($colunas) }}" class="px-5 py-10 text-center text-sm text-[#647168] dark:text-white/55">
                        Nenhum processo corresponde aos filtros.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="mt-5 flex flex-col gap-3 md:hidden">
        @foreach ($rows as $row)
            @php
                $busca = strtolower(trim(implode(' ', array_filter([
                    $row['nome'] ?? '',
                    $row['prontuario'] ?? '',
                    $row['disciplina'] ?? '',
                    $row['atividade'] ?? '',
                ]))));
            @endphp
            <div
                data-fila
                data-busca="{{ $busca }}"
                data-curso="{{ $row['curso'] }}"
                data-periodo="{{ $row['periodo'] }}"
                data-status="{{ $row['status'] }}"
                class="rounded-2xl border border-[#d9e1d9] bg-white p-4 dark:border-white/10 dark:bg-[#102319]"
            >
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0">
                        <p class="truncate text-sm font-semibold text-[#172019] dark:text-white">{{ $row['nome'] ?? $row['disciplina'] }}</p>
                        <p class="mt-0.5 text-xs text-[#647168] dark:text-white/55">
                            @isset($row['prontuario'])
                                Prontuário {{ $row['prontuario'] }}
                            @else
                                {{ $row['curso'] }} · {{ $row['periodo'] }}
                            @endisset
                        </p>
                    </div>
                    <span class="shrink-0 rounded-full border px-2.5 py-0.5 text-[11px] font-semibold {{ $statusStyles[$row['status']] }}">{{ $row['status'] }}</span>
                </div>

                <dl class="mt-3 space-y-1.5">
                    @foreach ($metaLabels as $metaKey => $metaLabel)
                        @if (in_array($metaKey, $colunas, true) && isset($row[$metaKey]))
                            <div class="flex gap-2 text-xs leading-5">
                                <dt class="w-24 shrink-0 text-[#647168] dark:text-white/50">{{ $metaLabel }}</dt>
                                <dd class="text-[#172019] dark:text-white/85">
                                    @if ($metaKey === 'curso')
                                        {{ $row['curso'] }} · {{ $row['periodo'] }}
                                    @elseif ($metaKey === 'disciplinas')
                                        {{ $row['disciplinas'] }} vinculadas
                                    @else
                                        {{ $row[$metaKey] }}
                                    @endif
                                </dd>
                            </div>
                        @endif
                    @endforeach
                </dl>

                <button type="button" class="mt-4 h-10 w-full rounded-lg border border-[#006437]/30 text-xs font-semibold text-[#006437] transition hover:bg-[#006437]/5 focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/15 dark:border-[#73c69b]/30 dark:text-[#73c69b] dark:hover:bg-[#73c69b]/10">
                    {{ $row['acao'] }}
                </button>
            </div>
        @endforeach
        <div class="js-vazio hidden rounded-2xl border border-[#d9e1d9] bg-white p-6 text-center text-sm text-[#647168] dark:border-white/10 dark:bg-[#102319] dark:text-white/55">
            Nenhum processo corresponde aos filtros.
        </div>
    </div>

    <p class="mt-4 flex items-start gap-2 text-xs leading-5 text-[#647168] dark:text-white/60">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <circle cx="12" cy="12" r="9" />
            <path stroke-linecap="round" d="M12 11v5m0-8.5v.5" />
        </svg>
        <span>Dados de demonstração. Processos, atividades e ações serão ligados ao módulo RED quando o back-end estiver disponível.</span>
    </p>
@endsection

@section('scripts')
    <script>
        (function () {
            var busca = document.getElementById('f-busca');
            var curso = document.getElementById('f-curso');
            var status = document.getElementById('f-status');
            var periodo = document.getElementById('f-periodo');
            var filas = Array.prototype.slice.call(document.querySelectorAll('[data-fila]'));
            var vazios = Array.prototype.slice.call(document.querySelectorAll('.js-vazio'));

            function normalizar(texto) {
                return (texto || '')
                    .toLowerCase()
                    .normalize('NFD')
                    .replace(/[\u0300-\u036f]/g, '');
            }

            function aplicar() {
                var termo = normalizar(busca.value.trim());
                var visiveis = 0;

                filas.forEach(function (linha) {
                    var ok =
                        (!termo || normalizar(linha.dataset.busca).indexOf(termo) !== -1) &&
                        (!curso.value || linha.dataset.curso === curso.value) &&
                        (!status.value || linha.dataset.status === status.value) &&
                        (!periodo.value || linha.dataset.periodo === periodo.value);

                    linha.classList.toggle('hidden', !ok);
                    if (ok) {
                        visiveis++;
                    }
                });

                vazios.forEach(function (bloco) {
                    bloco.classList.toggle('hidden', visiveis > 0);
                });
            }

            [busca, curso, status, periodo].forEach(function (campo) {
                campo.addEventListener('input', aplicar);
            });
        })();
    </script>
@endsection
