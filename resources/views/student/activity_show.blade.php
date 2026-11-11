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

    <section class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319]">
        <p class="text-sm text-[#172019] dark:text-white">{{ $activity->description }}</p>

        @if ($activity->url_activity)
            <a href="{{ \Illuminate\Support\Facades\Storage::url($activity->url_activity) }}" target="_blank" class="mt-3 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">
                Baixar atividade (PDF) →
            </a>
        @endif

        <p class="mt-3 text-xs text-[#647168] dark:text-white/55">Prazo: {{ $activity->deadline?->format('d/m/Y') ?? '—' }}</p>
        <div class="mt-2">
            <x-badge :status="$activity->status->value" :label="$activity->status->label()" />
        </div>
    </section>

    @if ($activity->status === \App\Enums\ActivityStatus::Sent)
        <x-form-panel
            title="Enviar resolução"
            subtitle="Envie um arquivo PDF ou um link para a sua resolução."
            :back="route('aluno.process.show')"
            back-label="Meu processo"
            :action="route('aluno.activity.submit', $activity)"
            method="POST"
        >
            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Forma de envio</label>
                    <div class="mt-3 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                            <input type="radio" name="resolution_type" value="file" checked class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]">
                            <span class="font-medium text-[#172019] dark:text-white">Arquivo PDF</span>
                        </label>
                        <label class="flex cursor-pointer items-center gap-3 rounded-lg border border-[#d9e1d9] px-4 py-3 text-sm transition hover:border-[#006437]/40 dark:border-white/10 dark:hover:border-[#73c69b]/40">
                            <input type="radio" name="resolution_type" value="link" class="h-4 w-4 accent-[#006437] dark:accent-[#73c69b]">
                            <span class="font-medium text-[#172019] dark:text-white">Link</span>
                        </label>
                    </div>
                </div>

                <div>
                    <label for="resolution_file" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Arquivo PDF da resolução</label>
                    <input id="resolution_file" name="resolution_file" type="file" accept="application/pdf" class="mt-2 block w-full text-sm text-[#172019] file:mr-4 file:rounded-lg file:border-0 file:bg-[#006437] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white hover:file:bg-[#004d2a] dark:text-white dark:file:bg-[#0f7a48] dark:hover:file:bg-[#0c6a3f]">
                </div>

                <div>
                    <label for="resolution_link" class="block text-xs font-bold tracking-[0.1em] text-[#647168] uppercase dark:text-white/55">Link da resolução</label>
                    <input id="resolution_link" name="resolution_link" type="url" placeholder="https://..." class="mt-2 h-11 w-full rounded-lg border border-[#d9e1d9] bg-white px-3.5 text-sm text-[#172019] outline-none transition focus:border-[#006437] focus:ring-4 focus:ring-[#006437]/10 dark:border-white/10 dark:bg-white/5 dark:text-white dark:focus:border-[#73c69b] dark:focus:ring-[#73c69b]/10">
                </div>
            </div>

            <x-slot:actions>
                <x-button variant="secondary" href="{{ route('aluno.process.show') }}">Cancelar</x-button>
                <x-button type="submit">Enviar resolução</x-button>
            </x-slot:actions>
        </x-form-panel>
    @endif

    @if ($activity->url_answer)
        <section class="mt-7 overflow-hidden rounded-2xl border border-[#d9e1d9] bg-white p-6 dark:border-white/10 dark:bg-[#102319]">
            <h2 class="text-sm font-semibold text-[#172019] dark:text-white">Sua resolução</h2>
            <p class="mt-1 text-xs text-[#647168] dark:text-white/55">Enviada em {{ $activity->answered_at?->format('d/m/Y H:i') ?? '—' }}</p>
            @if ($activity->answer_type === 'pdf')
                <a href="{{ \Illuminate\Support\Facades\Storage::url($activity->url_answer) }}" target="_blank" class="mt-2 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">Abrir PDF →</a>
            @else
                <a href="{{ $activity->url_answer }}" target="_blank" class="mt-2 inline-flex text-xs font-semibold text-[#006437] hover:underline dark:text-[#73c69b]">{{ $activity->url_answer }}</a>
            @endif

            @if ($activity->grade !== null)
                <p class="mt-3 text-xs font-semibold text-[#1e6b34] dark:text-[#8fd0a3]">Nota: {{ number_format($activity->grade, 2, ',', '.') }}</p>
            @endif
            @if ($activity->feedback)
                <p class="mt-1 text-xs text-[#172019] dark:text-white">{{ $activity->feedback }}</p>
            @endif
        </section>
    @endif
@endsection