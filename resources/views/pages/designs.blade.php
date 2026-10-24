<x-layouts.public
    title="Designs"
    description="Browse the Yamban Enterprise design library. Filter jersey, uniform, riding and hoodie designs by category, then open any design in Google Drive."
>
    <section class="relative overflow-hidden border-b border-ink/10">
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(11,12,14,0.06)_1px,transparent_0)] [background-size:26px_26px] [mask-image:linear-gradient(to_bottom,black,transparent_80%)]"></div>

        <div class="relative mx-auto grid max-w-7xl gap-8 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.15fr_0.85fr] lg:items-end">
            <div>
                <p class="label-xs flex items-center gap-3 text-flame">
                    <span class="h-px w-8 bg-flame"></span>
                    The design library
                </p>

                <h1 class="mt-6 text-[2.7rem] font-semibold leading-[0.95] tracking-[-0.045em] text-balance sm:text-5xl lg:text-[4rem]">
                    Start with a design. Make it yours.
                </h1>

                <p class="mt-5 max-w-xl text-base leading-relaxed text-ink/60">
                    Browse the concepts we have produced for teams and businesses. Filter by category, open a design in Google Drive, then tell us what to change — colors, logos and names are all editable.
                </p>
            </div>

            <div class="grid grid-cols-1 gap-2 sm:grid-cols-3 lg:grid-cols-1 lg:gap-2">
                @foreach ([
                    ['label' => 'Design files', 'value' => 'Open in Google Drive'],
                    ['label' => 'Categories', 'value' => 'Filter by sport or style'],
                    ['label' => 'Customizing', 'value' => 'Free proof before printing'],
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
        <livewire:design-catalog />
    </section>

    <section class="border-t border-ink/10 bg-white/50">
        <div class="mx-auto grid max-w-7xl gap-8 px-5 py-12 sm:px-8 md:grid-cols-3">
            <div>
                <p class="label-xs text-flame">Browse</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">Every design is a starting point.</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Filter by category to find a look that fits your sport, team or company, then open the file in Google Drive.</p>
            </div>

            <div>
                <p class="label-xs text-flame">Customize</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">Colors, logos, names and numbers.</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Send us the design name and the changes you want. We rebuild it with your branding and send a free proof for approval.</p>
            </div>

            <div>
                <p class="label-xs text-flame">Produce</p>
                <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">From proof to delivery.</h2>
                <p class="mt-2 text-sm leading-relaxed text-ink/55">Once the proof is approved we print, stitch and pack your order, then ship it anywhere in the Philippines.</p>
            </div>
        </div>
    </section>
</x-layouts.public>
