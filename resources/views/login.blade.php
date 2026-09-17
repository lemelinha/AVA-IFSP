<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#006437">
        <meta name="color-scheme" content="light dark">

        <title>Sistema RED — IFSP Campus Jacareí</title>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f2f5f1] font-sans text-[#172019] antialiased selection:bg-[#d12a2e] selection:text-white dark:bg-[#07120d] dark:text-white">
        <main class="min-h-screen lg:grid lg:grid-cols-[minmax(0,1.05fr)_minmax(440px,560px)]">
            <section class="relative isolate flex min-h-screen overflow-hidden bg-[#005c33] px-8 py-8 text-white sm:px-12 lg:px-16 xl:px-20">
                <div class="absolute inset-0 bg-[#004d2a]" aria-hidden="true"></div>
                <svg class="absolute inset-0 h-full w-full opacity-[0.16]" viewBox="0 0 1200 900" fill="none" aria-hidden="true">
                    <path d="M-40 690 C180 570 260 760 470 620 S820 420 1240 540" stroke="rgba(255,255,255,0.28)" stroke-width="1.5"/>
                    <path d="M-40 760 C210 650 330 820 540 690 S880 500 1240 620" stroke="rgba(255,255,255,0.16)" stroke-width="1.5"/>
                    <circle cx="930" cy="180" r="112" stroke="rgba(255,255,255,0.18)" stroke-width="1.5"/>
                    <circle cx="930" cy="180" r="72" stroke="rgba(255,255,255,0.12)" stroke-width="1.5"/>
                    <rect x="74" y="92" width="54" height="54" stroke="rgba(255,255,255,0.24)" stroke-width="1.5"/>
                    <rect x="142" y="92" width="54" height="54" stroke="rgba(255,255,255,0.14)" stroke-width="1.5"/>
                    <rect x="74" y="160" width="54" height="54" stroke="rgba(255,255,255,0.14)" stroke-width="1.5"/>
                    <rect x="142" y="160" width="54" height="54" stroke="rgba(255,255,255,0.24)" stroke-width="1.5"/>
                    <path d="M196 119h52v52h-52zM248 171h52v52h-52z" stroke="rgba(255,255,255,0.18)" stroke-width="1.5"/>
                </svg>
                <span class="pointer-events-none absolute right-[-1.75rem] top-[31%] select-none text-[13rem] font-bold leading-none tracking-tight text-white/[0.055] xl:right-[-2.5rem] xl:text-[17rem]" aria-hidden="true">RED</span>

                <div class="relative z-10 flex h-full flex-col justify-between">
                    <header class="flex items-center gap-4">
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
                    </header>

                    <div class="max-w-2xl">
                        <h1 class="max-w-3xl text-4xl font-semibold leading-[1.08] tracking-tight xl:text-6xl xl:leading-[1.06]">
                            Acompanhamento simples, seguro e colaborativo.
                        </h1>
                        <p class="mt-7 max-w-xl text-base leading-8 text-white/78 xl:text-lg xl:leading-9">
                            Conecte coordenadores, professores e alunos em um único fluxo para organizar atividades, resoluções e validações.
                        </p>

                        <div class="mt-12 grid gap-8 sm:grid-cols-3">
                            <div class="border-t border-white/25 pt-4">
                                <span class="text-xs font-semibold text-white/60">01</span>
                                <p class="mt-3 text-sm leading-6 text-white/88">Processos organizados por aluno, disciplina e professor.</p>
                            </div>
                            <div class="border-t border-white/25 pt-4">
                                <span class="text-xs font-semibold text-white/60">02</span>
                                <p class="mt-3 text-sm leading-6 text-white/88">Resoluções em PDF com acompanhamento de status.</p>
                            </div>
                            <div class="border-t border-white/25 pt-4">
                                <span class="text-xs font-semibold text-white/60">03</span>
                                <p class="mt-3 text-sm leading-6 text-white/88">Validação final após todas as atividades concluídas.</p>
                            </div>
                        </div>
                    </div>

                    <footer class="flex flex-col gap-3 text-xs leading-5 text-white/65 sm:flex-row sm:items-center sm:justify-between">
                        <span>Regime de Exercícios Domiciliares</span>
                        <span>© 2026 Instituto Federal de São Paulo — Campus Jacareí</span>
                    </footer>
                </div>
            </section>

            <section class="relative flex min-h-screen items-center justify-center bg-[#f2f5f1] px-5 py-10 dark:bg-[#07120d] sm:px-8 lg:px-14 xl:px-20">
                <div class="absolute inset-x-0 top-0 h-1 bg-[#d12a2e]" aria-hidden="true"></div>

                <div class="w-full max-w-[440px]">
                    <div class="mb-9 flex items-center gap-3 lg:hidden">
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
                            <span class="mt-1 block text-xs font-normal text-[#647168] dark:text-white/65">IFSP Campus Jacareí</span>
                        </div>
                    </div>

                    <div class="mb-8 flex items-start justify-between gap-6">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#006437] dark:text-[#73c69b]">Acesso ao sistema</p>
                            <h2 class="mt-3 text-2xl font-semibold leading-tight tracking-tight text-[#172019] dark:text-white">Olá, bem-vindo de volta.</h2>
                            <p class="mt-2 text-sm leading-6 text-[#647168] dark:text-white/65">Informe seus dados para acessar o Sistema RED.</p>
                        </div>
                        <div class="flex h-12 w-12 shrink-0 items-center justify-center border border-[#006437]/20 bg-white text-[#006437] dark:border-white/10 dark:bg-white/5 dark:text-[#73c69b]" aria-hidden="true">
                            <span class="text-xs font-bold tracking-tight">RED</span>
                        </div>
                    </div>

                    @if ($errors->any())
                        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-950 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-100" role="alert" aria-live="polite">
                            <p class="font-semibold">Não foi possível acessar.</p>
                            <ul class="mt-2 list-inside list-disc space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form class="rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319] sm:p-8" method="POST" action="{{ route('login.login') }}">
                        @csrf

                        <div class="space-y-6">
                            <div>
                                <label for="prontuario" class="mb-2 block text-sm font-medium text-[#172019] dark:text-white">Prontuário</label>
                                <input
                                    id="prontuario"
                                    name="prontuario"
                                    type="text"
                                    required
                                    autocomplete="username"
                                    autocapitalize="none"
                                    spellcheck="false"
                                    placeholder="Digite seu prontuário"
                                    class="h-12 w-full rounded-lg border border-[#d9e1d9] bg-white px-4 text-sm text-[#172019] placeholder:text-[#647168] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 focus-visible:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 dark:focus-visible:ring-[#73c69b]/10"
                                >
                            </div>

                            <div>
                                <label for="senha" class="mb-2 block text-sm font-medium text-[#172019] dark:text-white">Senha</label>
                                <input
                                    id="senha"
                                    name="senha"
                                    type="password"
                                    required
                                    autocomplete="current-password"
                                    placeholder="Digite sua senha"
                                    class="h-12 w-full rounded-lg border border-[#d9e1d9] bg-white px-4 text-sm text-[#172019] placeholder:text-[#647168] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 focus-visible:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:placeholder:text-white/40 dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10 dark:focus-visible:ring-[#73c69b]/10"
                                >
                            </div>

                            <div class="flex items-center justify-between gap-4">
                                <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-[#647168] dark:text-white/70">
                                    <input type="checkbox" name="remember" class="h-4 w-4 rounded border-[#cbd5cf] text-[#006437] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#006437]/40 dark:border-white/20 dark:bg-white/5">
                                    Lembrar acesso
                                </label>
                                <a href="#" class="shrink-0 text-sm font-medium text-[#006437] hover:text-[#004d2a] hover:underline dark:text-[#73c69b] dark:hover:text-white">Esqueceu a senha?</a>
                            </div>

                            <button
                                type="submit"
                                class="flex h-12 w-full items-center justify-center gap-2 rounded-lg bg-[#006437] px-5 text-sm font-semibold text-white transition hover:bg-[#004d2a] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#006437]/20 active:bg-[#004d2a]"
                            >
                                Entrar no sistema
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0 4-4m-4 4h14m-5 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h7a3 3 0 0 1 3 3v1" />
                                </svg>
                            </button>
                        </div>
                    </form>

                    <div class="mt-6 flex items-start gap-3 text-xs leading-5 text-[#647168] dark:text-white/45">
                        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.75a4.5 4.5 0 1 0-9 0c0 .53-.07 1.04-.2 1.53a3 3 0 0 0-1.8 2.72V16.5h13v-1.45a3 3 0 0 0-1.8-2.72 6.5 6.5 0 0 0-.2-1.53Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 14.25v3" />
                        </svg>
                        <span>Acesso exclusivo para usuários cadastrados pelo IFSP.</span>
                    </div>

                    <p class="mt-8 text-center text-xs leading-5 text-[#647168] dark:text-white/40">
                        Sistema RED v1.0<br class="sm:hidden"> · IFSP Campus Jacareí
                    </p>
                </div>
            </section>
        </main>
    </body>
</html>
