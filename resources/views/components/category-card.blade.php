@props(['category', 'index' => null])

<a
    href="{{ route('products', ['category' => $category->slug]) }}"
    class="group relative flex flex-col overflow-hidden rounded-3xl border border-ink/10 bg-white transition duration-300 hover:-translate-y-1 hover:border-ink/25 hover:shadow-[0_24px_60px_-30px_rgba(11,12,14,0.4)]"
>
    <div class="relative aspect-[4/5] overflow-hidden bg-sand">
        <img
            src="{{ $category->imageUrl() }}"
            alt="{{ $category->name }}"
            loading="lazy"
            class="size-full object-cover transition duration-700 group-hover:scale-105"
        >

        <div class="absolute inset-0 bg-gradient-to-t from-ink/75 via-ink/10 to-transparent"></div>

        @if ($index)
            <span class="absolute left-4 top-4 grid size-9 place-items-center rounded-full bg-bone/90 text-[11px] font-semibold tracking-wide text-ink backdrop-blur">
                {{ str_pad($index, 2, '0', STR_PAD_LEFT) }}
            </span>
        @endif

        <div class="absolute inset-x-0 bottom-0 p-5">
            <h3 class="text-xl font-semibold tracking-[-0.02em] text-white">{{ $category->name }}</h3>
            @if ($category->tagline)
                <p class="mt-1 text-sm text-white/70">{{ $category->tagline }}</p>
            @endif
        </div>
    </div>

    <div class="flex items-center justify-between gap-4 px-5 py-4">
        <span class="text-sm font-medium text-ink/55">Browse category</span>
        <span class="grid size-8 place-items-center rounded-full border border-ink/15 text-sm transition duration-300 group-hover:border-flame group-hover:bg-flame group-hover:text-white">→</span>
    </div>
</a>
