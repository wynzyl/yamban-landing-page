@props(['showTagline' => true])

<a href="{{ route('home') }}" class="group inline-flex items-center gap-3" aria-label="{{ config('site.name') }} home">
    <span class="grid size-10 shrink-0 place-items-center rounded-xl bg-flame text-lg font-semibold text-white transition-transform duration-300 group-hover:-rotate-6">
        Y
    </span>

    <span class="flex flex-col leading-none">
        <span class="text-[15px] font-semibold tracking-[0.16em] uppercase">Yamban</span>

        @if ($showTagline)
            <span class="mt-1.5 text-[10px] font-medium tracking-[0.28em] text-ink/45 uppercase">Enterprise</span>
        @endif
    </span>
</a>
