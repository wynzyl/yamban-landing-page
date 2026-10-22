@props(['product', 'featured' => false])

<article class="group flex flex-col overflow-hidden rounded-3xl border border-ink/10 bg-white transition duration-300 hover:-translate-y-1 hover:border-ink/25 hover:shadow-[0_24px_60px_-30px_rgba(11,12,14,0.35)]">
    <div class="relative aspect-square overflow-hidden bg-sand">
        <img
            src="{{ $product->imageUrl() }}"
            alt="{{ $product->name }}"
            loading="lazy"
            class="size-full object-cover transition duration-700 group-hover:scale-105"
        >

        <div class="absolute inset-x-4 top-4 flex items-start justify-between gap-2">
            <span class="rounded-full bg-bone/90 px-3 py-1.5 text-[10px] font-semibold tracking-[0.14em] text-ink uppercase backdrop-blur">
                {{ $product->category?->name }}
            </span>

            @if ($featured)
                <span class="shrink-0 rounded-full bg-flame px-3 py-1.5 text-[10px] font-semibold tracking-[0.14em] text-white uppercase">
                    Popular
                </span>
            @endif
        </div>
    </div>

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="flex items-start justify-between gap-4">
            <h3 class="text-lg font-semibold tracking-[-0.02em] text-balance">{{ $product->name }}</h3>

            @if ($product->price_from)
                <p class="shrink-0 text-right text-sm leading-tight">
                    <span class="block text-[10px] tracking-[0.14em] text-ink/40 uppercase">From</span>
                    <span class="font-semibold">{{ $product->price_from }}</span>
                </p>
            @endif
        </div>

        @if ($product->description)
            <p class="mt-2 text-sm leading-relaxed text-ink/55">{{ $product->description }}</p>
        @endif

        @if (filled($product->features))
            <ul class="mt-4 flex flex-wrap gap-1.5">
                @foreach (array_slice($product->features, 0, 3) as $feature)
                    <li class="rounded-full bg-bone px-2.5 py-1 text-[11px] font-medium text-ink/60">{{ $feature }}</li>
                @endforeach
            </ul>
        @endif

        <div class="mt-5 flex items-center justify-between gap-3 border-t border-ink/10 pt-4">
            <p class="text-xs text-ink/45">
                @if ($product->minimum_order)
                    Min. order <span class="font-semibold text-ink/70">{{ $product->minimum_order }}</span>
                @else
                    Custom made to order
                @endif
            </p>

            <a
                href="{{ route('contact', ['product' => $product->slug]) }}"
                class="inline-flex items-center gap-1.5 rounded-full border border-ink/15 px-3.5 py-2 text-xs font-semibold transition hover:border-ink hover:bg-ink hover:text-bone"
            >
                Inquire
                <span aria-hidden="true">→</span>
            </a>
        </div>
    </div>
</article>
