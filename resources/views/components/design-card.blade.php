@props(['design'])

@php($preview = $design->previewUrl())

<article class="group flex flex-col overflow-hidden rounded-3xl border border-ink/10 bg-white transition duration-300 hover:-translate-y-1 hover:border-ink/25 hover:shadow-[0_24px_60px_-30px_rgba(11,12,14,0.35)]">
    <a
        href="{{ $design->viewUrl() }}"
        target="_blank"
        rel="noopener noreferrer"
        class="relative block aspect-[4/3] overflow-hidden bg-sand"
        aria-label="{{ __('Open :name in Google Drive', ['name' => $design->name]) }}"
    >
        <div class="absolute inset-0 grid place-items-center bg-[radial-gradient(circle_at_1px_1px,rgba(11,12,14,0.08)_1px,transparent_0)] [background-size:18px_18px]">
            <span class="grid size-14 place-items-center rounded-full bg-bone/80 text-ink/40 backdrop-blur">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                    <rect x="3" y="4" width="18" height="16" rx="2.5" />
                    <circle cx="9" cy="10" r="1.7" />
                    <path d="m4.5 18 5-5 4 4 2.5-2.5 3.5 3.5" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </span>
        </div>

        @if ($preview)
            <img
                src="{{ $preview }}"
                alt="{{ $design->name }}"
                loading="lazy"
                class="relative size-full object-cover transition duration-700 group-hover:scale-105"
                onerror="this.style.display = 'none'"
            >
        @endif

        <span class="absolute left-4 top-4 rounded-full bg-bone/90 px-3 py-1.5 text-[10px] font-semibold tracking-[0.14em] text-ink uppercase backdrop-blur">
            {{ $design->category?->name }}
        </span>
    </a>

    <div class="flex flex-1 flex-col p-5">
        <h3 class="break-words text-base font-semibold leading-snug tracking-[-0.02em]">
            {{ $design->name }}
        </h3>

        <p class="mt-1.5 text-xs text-ink/45">{{ $design->category?->name }}</p>

        <div class="mt-auto flex items-center justify-between gap-3 border-t border-ink/10 pt-4">
            <span class="inline-flex items-center gap-1.5 text-[10px] font-medium tracking-[0.14em] text-ink/40 uppercase">
                <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M8 4h8l4 4v12H4V4h4Z" stroke-linejoin="round" />
                    <path d="M8 4v4h8V4M8 20v-6h8v6" stroke-linejoin="round" />
                </svg>
                Google Drive
            </span>

            <span class="inline-flex items-center gap-1.5 rounded-full border border-ink/15 px-3.5 py-2 text-xs font-semibold transition group-hover:border-ink group-hover:bg-ink group-hover:text-bone">
                View design
                <span aria-hidden="true">→</span>
            </span>
        </div>
    </div>
</article>
