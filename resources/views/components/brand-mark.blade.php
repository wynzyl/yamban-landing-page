@props(['showTagline' => true])

<a href="{{ route('home') }}" class="group inline-flex items-center gap-3" aria-label="{{ config('site.name') }} home">
    <img
        src="{{ asset('images/yamban-logo.jpg') }}"
        alt="{{ config('site.name') }} logo"
        class="h-12 w-auto transition-transform duration-300 group-hover:scale-105"
    >

    @if ($showTagline)
        <span class="text-[15px] font-semibold tracking-[0.16em] uppercase">Yamban</span>
    @endif
</a>
