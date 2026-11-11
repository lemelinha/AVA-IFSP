<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#006437">
        <meta name="color-scheme" content="light dark">

        <title>@yield('title', 'Sistema RED — IFSP Campus Jacareí')</title>

        <script>
            (function () {
                var stored = null;

                try {
                    stored = localStorage.getItem('ava-theme');
                } catch (error) {
                    // localStorage indisponível — segue a preferência do sistema.
                }

                var dark = stored === 'dark'
                    || (stored !== 'light' && window.matchMedia('(prefers-color-scheme: dark)').matches);

                document.documentElement.classList.toggle('dark', dark);
            })();
        </script>

        @fonts
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-[#f2f5f1] font-sans text-[#172019] antialiased selection:bg-[#d12a2e] selection:text-white dark:bg-[#07120d] dark:text-white">
        <div class="h-1 bg-[#d12a2e]" aria-hidden="true"></div>

        <header class="border-b border-[#d9e1d9] bg-white dark:border-white/10 dark:bg-[#102319]">
            <div class="mx-auto flex h-16 w-full max-w-[1440px] items-center justify-between gap-4 px-5 sm:px-8 lg:px-12">
                <a href="{{ route('home') }}" class="flex items-center gap-3" aria-label="Sistema RED — início">
                    <img
                        src="{{ asset('images/if-logo.png') }}"
                        alt=""
                        width="36"
                        height="36"
                        class="h-9 w-auto shrink-0"
                        aria-hidden="true"
                    >
                    <span class="text-sm font-semibold leading-tight">
                        Sistema RED
                        <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/65">IFSP Campus Jacareí</span>
                    </span>
                </a>

                @hasanyrole(['cti'])
                    <nav aria-label="Área CTI" class="mr-auto hidden items-center gap-5 text-sm font-medium md:flex">
                        <a href="{{ route('cti.processes.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Processos</a>
                        <a href="{{ route('cti.users.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Usuários</a>
                        <a href="{{ route('cti.courses.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Cursos</a>
                        <a href="{{ route('cti.disciplines.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Disciplinas</a>
                    </nav>
                @elsehasanyrole(['coordinator'])
                    <nav aria-label="Área do coordenador" class="mr-auto hidden items-center gap-5 text-sm font-medium md:flex">
                        <a href="{{ route('coordinator.processes.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Processos</a>
                        <a href="{{ route('coordinator.students.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Alunos</a>
                    </nav>
                @elsehasanyrole(['professor'])
                    <nav aria-label="Área do professor" class="mr-auto hidden items-center gap-5 text-sm font-medium md:flex">
                        <a href="{{ route('professor.processes.index') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Processos</a>
                    </nav>
                @elsehasanyrole(['student'])
                    <nav aria-label="Área do aluno" class="mr-auto hidden items-center gap-5 text-sm font-medium md:flex">
                        <a href="{{ route('aluno.process.show') }}" class="text-[#647168] transition hover:text-[#006437] dark:text-white/65 dark:hover:text-[#73c69b]">Meu processo</a>
                    </nav>
                @endhasanyrole

                <div class="flex items-center gap-3 sm:gap-4">
                    <div class="hidden items-center gap-2 sm:flex">
                        <x-role-badges :roles="auth()->user()->roles" />
                    </div>

                    <a href="{{ route('user.profile') }}" class="group flex items-center gap-3" aria-label="Abrir perfil de {{ auth()->user()->name }}">
                        <div class="hidden text-right text-sm leading-tight sm:block">
                            <span class="font-medium transition group-hover:text-[#006437] dark:group-hover:text-[#73c69b]">{{ auth()->user()->name }}</span>
                            <span class="mt-0.5 block text-xs font-normal text-[#647168] dark:text-white/65">Prontuário {{ auth()->user()->prontuario }}</span>
                        </div>

                        <span class="flex h-9 w-9 shrink-0 items-center justify-center bg-[#006437] text-xs font-semibold text-white transition group-hover:bg-[#004d2a]" aria-hidden="true">
                            {{ auth()->user()->initials() }}
                        </span>
                    </a>

                    <button
                        type="button"
                        data-theme-toggle
                        aria-pressed="false"
                        aria-label="Ativar modo escuro"
                        class="flex h-9 w-9 shrink-0 items-center justify-center border border-[#d9e1d9] text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#d12a2e]/15 dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]"
                    >
                        <svg data-theme-sun class="hidden h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <circle cx="12" cy="12" r="4" />
                            <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32 1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41M17.66 6.34l1.41-1.41" />
                        </svg>
                        <svg data-theme-moon class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79Z" />
                        </svg>
                    </button>

                    <form method="POST" action="{{ route('login.logout') }}">
                        @csrf
                        <button type="submit" class="flex h-9 items-center gap-1.5 border border-[#d9e1d9] px-3 text-xs font-semibold text-[#647168] transition hover:border-[#d12a2e] hover:text-[#d12a2e] focus:outline-none focus-visible:ring-4 focus-visible:ring-[#d12a2e]/15 dark:border-white/10 dark:text-white/70 dark:hover:border-[#d12a2e] dark:hover:text-[#f2a3a5]">
                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0-4-4m4 4H7m6 4v1a3 3 0 0 1-3 3H6a3 3 0 0 1-3-3V7a3 3 0 0 1 3-3h4a3 3 0 0 1 3 3v1" />
                            </svg>
                            Sair
                        </button>
                    </form>
                </div>
            </div>
        </header>

        <main class="mx-auto w-full max-w-[1440px] px-5 py-8 sm:px-8 lg:px-12">
            @yield('content')
        </main>

        <footer class="border-t border-[#d9e1d9] bg-transparent dark:border-white/10">
            <div class="mx-auto flex w-full max-w-[1440px] flex-col gap-1 px-5 py-6 text-xs leading-5 text-[#647168] sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12 dark:text-white/60">
                <span>Regime de Exercícios Domiciliares</span>
                <span>Sistema RED v1.0 · IFSP Campus Jacareí</span>
            </div>
        </footer>

        @yield('scripts')
    </body>
</html>
