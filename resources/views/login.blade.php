<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#006437">

        <title>Sistema RED — IFSP Campus Jacareí</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f5f7fa] font-sans text-[#1a1d23] antialiased selection:bg-[#006437] selection:text-white dark:bg-[#0b1510] dark:text-white">
        <main class="grid min-h-screen lg:grid-cols-[minmax(0,1fr)_minmax(420px,520px)]">
            <section class="relative hidden overflow-hidden bg-[#006437] text-white lg:flex lg:flex-col lg:justify-between lg:p-10 xl:p-16">
                <div class="absolute -left-24 -top-24 h-80 w-80 rounded-full border border-white/10"></div>
                <div class="absolute -bottom-32 -right-24 h-96 w-96 rounded-full border border-white/10"></div>
                <div class="absolute bottom-16 left-1/2 h-px w-[120%] -translate-x-1/2 bg-white/10"></div>

                <div class="relative z-10 flex items-center gap-4">
                    <svg class="h-14 w-12 shrink-0" viewBox="0 0 35 50" fill="none" aria-hidden="true">
                        <circle cx="5" cy="5" r="4.375" fill="#d12a2e" />
                        <rect x="12.5" y="1.25" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="23.75" y="1.25" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="1.25" y="12.5" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="12.5" y="12.5" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="1.25" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="12.5" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="23.75" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="1.25" y="35" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        <rect x="12.5" y="35" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                    </svg>
                    <div class="text-sm font-semibold leading-tight">
                        Sistema RED
                        <span class="mt-1 block text-xs font-normal text-white/70">IFSP Campus Jacareí</span>
                    </div>
                </div>

                <div class="relative z-10 max-w-xl">
                    <p class="mb-4 text-xs font-semibold uppercase tracking-[0.2em] text-white/70">Regime de Exercícios Domiciliares</p>
                    <h1 class="max-w-lg text-4xl font-semibold leading-tight xl:text-5xl">Acompanhamento simples, seguro e colaborativo.</h1>
                    <p class="mt-6 max-w-lg text-base leading-7 text-white/75 xl:text-lg">
                        Conecte coordenadores, professores e alunos em um único fluxo para organizar atividades, resoluções e validações.
                    </p>

                    <ul class="mt-10 space-y-4 text-sm leading-6 text-white/85">
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                </svg>
                            </span>
                            Processos organizados por aluno, disciplina e professor.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                </svg>
                            </span>
                            Resoluções em PDF com acompanhamento de status.
                        </li>
                        <li class="flex items-start gap-3">
                            <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7" />
                                </svg>
                            </span>
                            Validação final após todas as atividades concluídas.
                        </li>
                    </ul>
                </div>

                <div class="relative z-10 text-xs text-white/65">© 2026 Instituto Federal de São Paulo — Campus Jacareí</div>
            </section>

            <section class="flex items-center justify-center px-6 py-12 sm:px-10 lg:px-16">
                <div class="w-full max-w-md">
                    <div class="mb-10 flex items-center gap-3 lg:hidden">
                        <svg class="h-11 w-10 shrink-0" viewBox="0 0 35 50" fill="none" aria-hidden="true">
                            <circle cx="5" cy="5" r="4.375" fill="#d12a2e" />
                            <rect x="12.5" y="1.25" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="23.75" y="1.25" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="1.25" y="12.5" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="12.5" y="12.5" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="1.25" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="12.5" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="23.75" y="23.75" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="1.25" y="35" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                            <rect x="12.5" y="35" width="8.75" height="8.75" rx="1.25" fill="#3c8c2f" />
                        </svg>
                        <div class="text-sm font-semibold leading-tight text-[#006437] dark:text-white">
                            Sistema RED
                            <span class="mt-1 block text-xs font-normal text-[#6b7280] dark:text-white/65">IFSP Campus Jacareí</span>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-[#dde1e6] bg-white p-8 shadow-[0_24px_80px_-24px_rgba(0,0,0,0.25)] sm:p-10 dark:border-white/10 dark:bg-[#102319] dark:shadow-none">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#006437] dark:text-[#73c69b]">Acesso ao sistema</p>
                            <h2 class="mt-3 text-2xl font-semibold text-[#1a1d23] dark:text-white">Olá, bem-vindo de volta.</h2>
                            <p class="mt-2 text-sm leading-6 text-[#6b7280] dark:text-white/65">Informe seus dados para acessar o Sistema RED.</p>
                        </div>

                        <form class="mt-9 space-y-6" method="POST" action="#">
                            @csrf

                            <div>
                                <label for="prontuario" class="mb-2 block text-sm font-medium text-[#1a1d23] dark:text-white">Prontuário</label>
                                <input
                                    id="prontuario"
                                    name="prontuario"
                                    type="text"
                                    required
                                    autocomplete="username"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    placeholder="Digite seu prontuário"
                                    class="h-11 w-full rounded-lg border border-[#dde1e6] bg-white px-4 text-sm text-[#1a1d23] placeholder:text-[#9ca3af] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                                >
                            </div>

                            <div>
                                <label for="senha" class="mb-2 block text-sm font-medium text-[#1a1d23] dark:text-white">Senha</label>
                                <input
                                    id="senha"
                                    name="senha"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Digite sua senha"
                                    class="h-11 w-full rounded-lg border border-[#dde1e6] bg-white px-4 text-sm text-[#1a1d23] placeholder:text-[#9ca3af] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10"
                                >
                            </div>

                            <div class="flex items-center justify-between">
                                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-[#6b7280] dark:text-white/65">
                                    <input type="checkbox" class="h-4 w-4 rounded border-[#dde1e6] text-[#006437] focus:ring-[#006437] dark:border-white/10 dark:bg-white/5">
                                    Lembrar acesso
                                </label>
                                <a href="#" class="text-sm font-medium text-[#006437] hover:text-[#004d2a] hover:underline dark:text-[#73c69b] dark:hover:text-white">Esqueceu a senha?</a>
                            </div>

                            <button
                                type="submit"
                                class="flex h-11 w-full items-center justify-center gap-2 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#004d2a] focus:outline-none focus:ring-4 focus:ring-[#006437]/20 active:bg-[#004d2a]"
                            >
                                Entrar no sistema
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0 4-4m-4 4h14m-5 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1" />
                                </svg>
                            </button>
                        </form>

                        <div class="mt-8 flex items-center gap-3 text-xs leading-5 text-[#9ca3af] dark:text-white/45">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.75a4.5 4.5 0 1 0-9 0c0 .53-.07 1.04-.2 1.53a3 3 0 0 0-1.8 2.72V16.5h13v-1.45a3 3 0 0 0-1.8-2.72 6.5 6.5 0 0 0-.2-1.53Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25v3" />
                            </svg>
                            <span>Acesso exclusivo para usuários cadastrados pelo IFSP.</span>
                        </div>
                    </div>

                    <p class="mt-7 text-center text-xs leading-5 text-[#9ca3af] dark:text-white/45">
                        Sistema RED v1.0<br class="sm:hidden"> · IFSP Campus Jacareí
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
