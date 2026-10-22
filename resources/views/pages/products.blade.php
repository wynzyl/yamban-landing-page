<x-layouts.public
    title="Products"
    description="Browse custom athletic jerseys, corporate uniforms, riding longsleeves, hoodies and jackets from Yamban Enterprise. Filter by category and send an inquiry for a quote."
>
    <section class="relative overflow-hidden border-b border-ink/10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(11,12,14,0.06)_1px,transparent_0)] [background-size:26px_26px] [mask-image:linear-gradient(to_bottom,black,transparent_80%)]"></div>

        <div class="relative mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
            <div>
                <p class="label-xs flex items-center gap-3 text-flame">
                    <span class="h-px w-8 bg-flame"></span>
                    The catalog
                </p>

                <h1 class="mt-6 text-[2.7rem] font-semibold leading-[0.95] tracking-[-0.045em] text-balance sm:text-5xl lg:text-[4rem]">
                    Gear made for your team, your way.
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-ink/60">
                    Filter by category or search for a piece. Every product on this page is made to order, so colors, logos, names and sizes are yours to choose.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 lg:grid-cols-1 lg:gap-2">
                @foreach ([
                    ['label' => 'Lead time', 'value' => '7–15 working days'],
                    ['label' => 'Minimum order', 'value' => 'From 5 pcs'],
                    ['label' => 'Design proof', 'value' => 'Free before printing'],
                ] as $item)
                    <div class="flex items-center justify-between gap-3 rounded-2xl border border-ink/10 bg-white/70 px-4 py-3 sm:block lg:flex lg:items-center lg:justify-between lg:px-5">
                        <p class="text-[10px] font-medium tracking-[0.16em] text-ink/40 uppercase lg:text-[11px]">{{ $item['label'] }}</p>
                        <p class="text-sm font-semibold sm:mt-1 lg:mt-0">{{ $item['value'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        <livewire:product-catalog />
    </section>

    <section class="border-t border-ink/10 bg-white/50">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-12 sm:px-8 md:grid-cols-3">
            <div>
                <p class="label-xs text-flame">Sizing</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">Sizes for every body on the team.</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Youth to 3XL on most styles. Send measurements if you need a closer fit — we'll build the size run with you.</p>
            </div>

            <div>
                <p class="label-xs text-flame">Delivery</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">Nationwide shipping.</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Courier delivery anywhere in the Philippines, or arrange a pickup if your team is nearby. Bulk orders are packed by size.</p>
            </div>

            <div>
                <p class="label-xs text-flame">Custom work</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">Nothing here quite right?</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Send a reference photo or a rough sketch. We regularly produce pieces that aren't in the catalog.</p>
            </div>
        </div>
    </section>
</x-layouts.public>
