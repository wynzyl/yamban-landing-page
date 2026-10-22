@php
    $navigation = [
        ['label' => 'Home', 'route' => 'home'],
        ['label' => 'About', 'route' => 'about'],
        ['label' => 'Products', 'route' => 'products'],
        ['label' => 'Contact', 'route' => 'contact'],
    ];
@endphp

<div class="border-b border-ink/10 bg-ink text-bone">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-4 px-5 py-2.5 sm:px-8">
        <p class="flex items-center gap-2 text-[10px] font-medium tracking-[0.12em] text-bone/70 uppercase sm:text-[11px] sm:tracking-[0.22em]">
            <span class="size-1.5 shrink-0 rounded-full bg-flame"></span>
            <span class="sm:hidden">Made in the Philippines</span>
            <span class="hidden sm:inline">Custom-made in the Philippines</span>
            <span class="hidden text-bone/30 sm:inline">/</span>
            <span class="hidden sm:inline">Nationwide shipping</span>
        </p>

        <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="shrink-0 whitespace-nowrap text-[10px] font-medium tracking-[0.12em] text-bone/70 uppercase transition hover:text-white sm:text-[11px] sm:tracking-[0.22em]">
            Follow on Facebook
        </a>
    </div>
</div>

<header class="sticky top-0 z-50 border-b border-ink/10 bg-bone/90 backdrop-blur-md">
    <div class="mx-auto flex max-w-7xl items-center justify-between gap-6 px-5 py-4 sm:px-8">
        <x-brand-mark />

        <nav class="hidden items-center gap-1 md:flex" aria-label="Primary">
            @foreach ($navigation as $item)
                @php($isActive = request()->routeIs($item['route']))
                <a
                    href="{{ route($item['route']) }}"
                    @class([
                        'relative rounded-full px-4 py-2 text-sm font-medium transition',
                        'bg-ink text-bone' => $isActive,
                        'text-ink/60 hover:text-ink' => ! $isActive,
                    ])
                    @if ($isActive) aria-current="page" @endif
                >
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="hidden items-center gap-3 md:flex">
            <a
                href="{{ config('site.facebook') }}"
                target="_blank"
                rel="noopener noreferrer"
                class="text-sm font-medium text-ink/60 transition hover:text-ink"
            >
                Facebook
            </a>

            <a
                href="{{ route('contact') }}"
                class="group inline-flex items-center gap-2 rounded-full bg-flame px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-flame-deep"
            >
                Get a quote
                <span class="transition-transform duration-300 group-hover:translate-x-0.5">→</span>
            </a>
        </div>

        <details class="group md:hidden">
            <summary
                class="grid size-10 cursor-pointer list-none place-items-center rounded-full border border-ink/15 [&::-webkit-details-marker]:hidden"
                aria-label="Toggle navigation"
            >
                <svg class="size-5 group-open:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" stroke-linecap="round" />
                </svg>
                <svg class="hidden size-5 group-open:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="M6 6l12 12M18 6L6 18" stroke-linecap="round" />
                </svg>
            </summary>

            <div class="absolute inset-x-0 top-full border-t border-ink/10 bg-bone px-5 pb-6 pt-4 shadow-[0_24px_40px_-30px_rgba(11,12,14,0.5)]">
                <nav class="flex flex-col" aria-label="Mobile">
                    @foreach ($navigation as $item)
                        <a
                            href="{{ route($item['route']) }}"
                            @class([
                                'flex items-center justify-between border-b border-ink/10 py-3.5 text-lg font-medium',
                                'text-flame' => request()->routeIs($item['route']),
                                'text-ink' => ! request()->routeIs($item['route']),
                            ])
                        >
                            {{ $item['label'] }}
                            <span class="text-sm text-ink/30">0{{ $loop->iteration }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="mt-5 flex flex-col gap-3">
                    <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-flame px-5 py-3 text-sm font-semibold text-white">
                        Get a quote →
                    </a>
                    <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full border border-ink/15 px-5 py-3 text-sm font-semibold">
                        Message us on Facebook
                    </a>
                </div>
            </div>
        </details>
    </div>
</header>
