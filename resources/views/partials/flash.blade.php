@if (session('status'))
    <div class="mb-5 flex items-start gap-2 rounded-xl border border-[#c4e2cc] bg-[#e7f4ea] px-4 py-3 text-sm leading-5 text-[#1e6b34] dark:border-[#8fd0a3]/30 dark:bg-[#e7f4ea]/10 dark:text-[#8fd0a3]">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
        </svg>
        <span>{{ session('status') }}</span>
    </div>
@endif

@if ($errors->any())
    <div class="mb-5 flex items-start gap-2 rounded-xl border border-[#f3c9c9] bg-[#fdeaea] px-4 py-3 text-sm leading-5 text-[#a4262c] dark:border-[#f2a3a5]/30 dark:bg-[#fdeaea]/10 dark:text-[#f2a3a5]">
        <svg class="mt-0.5 h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" d="M12 9v4m0 4h.01M10.29 3.86l-8.02 13.9A2 2 0 0 0 3.98 21h16.04a2 2 0 0 0 1.71-3.24L13.71 3.86a2 2 0 0 0-3.42 0Z" />
        </svg>
        <ul class="list-disc space-y-1 ps-4">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
