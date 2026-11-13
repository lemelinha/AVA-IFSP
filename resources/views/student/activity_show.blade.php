@extends('layouts.app')

@section('title', $activity->title . ' — Atividade')

@section('content')
    <x-page-header
        :title="$activity->title"
        :subtitle="$activity->discipline->label . ' · Professor: ' . $activity->professor->name"
        :back="route('aluno.process.show')"
        back-label="Meu processo"
    />

    @include('partials.flash')

    @php
        $isOverdue = $activity->deadline !== null
            && $activity->deadline->isPast()
            && ! in_array($activity->status, [\App\Enums\ActivityStatus::Corrected, \App\Enums\ActivityStatus::Concluded], true);
        $canAnswer = $activity->process->status === \App\Enums\ProcessStatus::Active
            && in_array($activity->status, [\App\Enums\ActivityStatus::Sent, \App\Enums\ActivityStatus::InCorrection], true);
    @endphp

    <section class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319]">
        <div class="flex flex-wrap items-center gap-2">
            <x-badge :status="$activity->status->value" />
            @if ($isOverdue)
                <x-badge status="overdue" />
            @endif
        </div>

        <p class="mt-3 text-sm leading-6 text-[#172019] dark:text-white">{{ $activity->description }}</p>

        @include('partials.activity_material', ['activity' => $activity])

        <p class="mt-3 text-xs text-[#647168] dark:text-white/55">Prazo: {{ $activity->deadline?->format('d/m/Y') ?? '—' }}</p>

        <p class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-xs text-[#647168] dark:text-white/55">
            @if ($activity->published_at)
                <span>Publicada em {{ $activity->published_at->format('d/m/Y H:i') }}</span>
            @endif
            @if ($activity->answered_at)
                <span>Respondida em {{ $activity->answered_at->format('d/m/Y H:i') }}</span>
            @endif
            @if ($activity->corrected_at)
                <span>Corrigida em {{ $activity->corrected_at->format('d/m/Y H:i') }}</span>
            @endif
            @if ($activity->concluded_at)
                <span>Validada em {{ $activity->concluded_at->format('d/m/Y H:i') }}</span>
            @endif
        </p>
    </section>

    @if ($activity->url_answer || $activity->answer_text)
        <div class="mt-5">
            @include('partials.activity_answer', ['activity' => $activity, 'heading' => 'Sua resolução'])
        </div>
    @endif

    @if ($canAnswer)
        <x-form-panel
            title="{{ $activity->url_answer || $activity->answer_text ? 'Reenviar resolução' : 'Enviar resolução' }}"
            subtitle="Envie um arquivo PDF, um link (ex.: Drive) ou escreva a resolução."
            :action="route('aluno.activity.submit', $activity)"
            method="POST"
        >
            <div class="space-y-5">
                <div>
                    <span class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Forma de envio</span>
                    <div class="mt-3 grid gap-3 sm:grid-cols-3">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                            <input type="radio" name="resolution_type" value="file" @checked(old('resolution_type', 'file') === 'file') class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]">
                            <span class="font-medium text-[#172019] dark:text-white">Arquivo PDF</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                            <input type="radio" name="resolution_type" value="link" @checked(old('resolution_type') === 'link') class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]">
                            <span class="font-medium text-[#172019] dark:text-white">Link</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                            <input type="radio" name="resolution_type" value="text" @checked(old('resolution_type') === 'text') class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]">
                            <span class="font-medium text-[#172019] dark:text-white">Texto</span>
                        </label>
                    </div>
                    @error('resolution_type')
                        <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                    @enderror
                </div>

                <div id="field-file">
                    <label for="resolution_file" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Arquivo PDF da resolução</label>
                    <input id="resolution_file" name="resolution_file" type="file" accept="application/pdf" class="mt-2 block w-full text-sm text-[#172019] file:mr-4 file:rounded-lg file:border-0 file:bg-[#006437] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#004d2a] dark:text-white dark:file:bg-[#0f7a48] dark:hover:file:bg-[#0c6a3f]">
                    <p class="mt-1.5 text-xs text-[#647168] dark:text-white/55">Tamanho máximo: 5 MB.</p>
                    @error('resolution_file')
                        <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                    @enderror
                </div>

                <div id="field-link" class="hidden">
                    <label for="resolution_link" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Link da resolução</label>
                    <input id="resolution_link" name="resolution_link" type="url" placeholder="https://..." class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                    @error('resolution_link')
                        <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                    @enderror
                </div>

                <div id="field-text" class="hidden">
                    <label for="resolution_text" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Resolução</label>
                    <textarea id="resolution_text" name="resolution_text" rows="8" class="mt-2 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 py-2.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">{{ old('resolution_text') }}</textarea>
                    @error('resolution_text')
                        <p class="mt-1.5 text-xs font-medium text-[#a4262c] dark:text-[#f2a3a5]">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('aluno.process.show') }}">Cancelar</x-button>
                <x-button type="submit">Enviar resolução</x-button>
            </x-slot:actions>
        </x-form-panel>
    @elseif ($activity->process->status === \App\Enums\ProcessStatus::Concluded)
        <p class="mt-5 rounded-xl border border-[#f0dcb0] bg-[#fdf3e0] px-4 py-3 text-sm text-[#8a5a00] dark:border-[#e8c07a]/30 dark:bg-[#fdf3e0]/10 dark:text-[#e8c07a]">
            O processo foi finalizado e não aceita mais respostas.
        </p>
    @endif
@endsection

@section('scripts')
    <script>
        (function () {
            var radios = document.querySelectorAll('input[name="resolution_type"]');
            var fields = {
                file: document.getElementById('field-file'),
                link: document.getElementById('field-link'),
                text: document.getElementById('field-text'),
            };

            function apply() {
                var selected = document.querySelector('input[name="resolution_type"]:checked');

                Object.keys(fields).forEach(function (key) {
                    fields[key].classList.toggle('hidden', !selected || selected.value !== key);
                });
            }

            radios.forEach(function (radio) {
                radio.addEventListener('change', apply);
            });

            apply();
        })();
    </script>
@endsection
