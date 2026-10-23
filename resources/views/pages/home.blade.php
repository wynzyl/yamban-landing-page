<x-layouts.public description="Yamban Enterprise makes custom athletic jerseys, corporate uniforms, riding longsleeves, hoodies and jackets for teams and businesses across the Philippines.">
    {{-- Hero --}}
    <section class="relative overflow-hidden">
        <div class="pointer-events-none absolute -right-40 -top-40 size-[32rem] rounded-full bg-flame/10 blur-3xl"></div>
        <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_1px_1px,rgba(11,12,14,0.06)_1px,transparent_0)] [background-size:26px_26px] [mask-image:linear-gradient(to_bottom,black,transparent_70%)]"></div>

        <div class="relative mx-auto grid max-w-7xl items-center gap-12 px-5 pb-16 pt-12 sm:px-8 lg:grid-cols-[1.02fr_0.98fr] lg:gap-16 lg:pb-24 lg:pt-20">
            <div>
                <h1 class="text-[2.9rem] font-semibold leading-[0.94] tracking-[-0.045em] text-balance sm:text-6xl lg:text-[4.5rem]">
                    Your team.<br>
                    Your colors.<br>
                    <span class="text-flame">Made to order.</span>
                </h1>

                <p class="mt-6 max-w-lg text-base leading-relaxed text-ink/60 sm:text-lg">
                    Jerseys, corporate uniforms, riding longsleeves, hoodies and jackets — designed with you, printed and stitched by Yamban Enterprise, and delivered anywhere in the Philippines.
                </p>

                <div class="mt-8 flex flex-wrap items-center gap-3">
                    <a href="{{ route('products') }}" class="group inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3.5 text-sm font-semibold text-bone transition hover:bg-ink-soft">
                        Browse products
                        <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                    </a>

                    <a href="{{ route('contact') }}" class="inline-flex items-center gap-2 rounded-full border border-ink/20 px-6 py-3.5 text-sm font-semibold transition hover:border-ink hover:bg-white">
                        Start an inquiry
                    </a>
                </div>

                <dl class="mt-12 grid max-w-lg grid-cols-3 gap-4 pt-2">
                    <div>
                        <dt class="text-sm font-semibold">Free proof</dt>
                        <dd class="mt-1 text-xs leading-relaxed text-ink/45">See your design before we print.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold">Low minimums</dt>
                        <dd class="mt-1 text-xs leading-relaxed text-ink/45">From 5–20 pcs per design.</dd>
                    </div>
                    <div>
                        <dt class="text-sm font-semibold">Nationwide</dt>
                        <dd class="mt-1 text-xs leading-relaxed text-ink/45">Shipping across the Philippines.</dd>
                    </div>
                </dl>
            </div>

            <div class="relative">
                <div class="grid h-[30rem] grid-cols-5 grid-rows-6 gap-3 sm:h-[34rem] lg:h-[36rem]">
                    <div class="relative col-span-3 row-span-6 overflow-hidden rounded-[2rem] bg-sand">
                        <img src="{{ asset('images/brand/hero-main.jpg') }}" alt="Football player wearing a custom jersey" class="size-full object-cover">
                    </div>

                    <div class="col-span-2 row-span-3 overflow-hidden rounded-[1.5rem] bg-sand">
                        <img src="{{ asset('images/brand/hero-secondary.jpg') }}" alt="Motorcycle rider on an open road" class="size-full object-cover">
                    </div>

                    <div class="col-span-2 row-span-3 overflow-hidden rounded-[1.5rem] bg-sand">
                        <img src="{{ asset('images/brand/hero-tertiary.jpg') }}" alt="Person wearing a grey pullover hoodie" class="size-full object-cover">
                    </div>
                </div>

                <div class="absolute -bottom-5 left-4 flex items-center gap-3 rounded-2xl border border-ink/10 bg-bone px-4 py-3 shadow-[0_18px_40px_-24px_rgba(11,12,14,0.6)] sm:left-8">
                    <span class="grid size-9 place-items-center rounded-full bg-flame text-white">
                        <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                            <path d="M4 20l4-1 10-10a2.8 2.8 0 0 0-4-4L4 15l-1 5Z" stroke-linejoin="round" />
                            <path d="m13 6 4 4" />
                        </svg>
                    </span>
                    <span>
                        <span class="block text-sm font-semibold">Design to delivery</span>
                        <span class="block text-xs text-ink/45">One team handles it all</span>
                    </span>
                </div>
            </div>
        </div>
    </section>

    {{-- Category ticker --}}
    <section class="border-y border-ink/10 bg-white/60">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-x-8 gap-y-3 px-5 py-4 sm:px-8">
            @foreach ($categories as $category)
                <a href="{{ route('products', ['category' => $category->slug]) }}" class="group inline-flex items-center gap-3 text-sm font-medium text-ink/70 transition hover:text-ink">
                    {{ $category->name }}
                    <span class="size-1 rounded-full bg-flame/60 transition group-hover:bg-flame"></span>
                </a>
            @endforeach
        </div>
    </section>

    {{-- Categories --}}
    <section id="categories" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
        <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
            <x-section-heading
                eyebrow="The catalog"
                title="Five ways to kit out your team."
                description="Start with a category, then tell us the details. Every piece is made to order — no off-the-rack compromises."
            />

            <a href="{{ route('products') }}" class="group inline-flex shrink-0 items-center gap-2 text-sm font-semibold">
                View all products
                <span class="grid size-8 place-items-center rounded-full border border-ink/15 transition group-hover:border-flame group-hover:bg-flame group-hover:text-white">→</span>
            </a>
        </div>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($categories as $category)
                <x-category-card :category="$category" :index="$loop->iteration" wire:key="home-category-{{ $category->id }}" />
            @endforeach

            <div class="flex flex-col justify-between gap-8 rounded-3xl bg-ink p-6 text-bone sm:p-7">
                <div>
                    <h3 class="text-2xl font-semibold tracking-[-0.03em] text-balance">
                        Send us a photo or a rough idea. We'll shape it into something you can wear.
                    </h3>
                </div>

                <div>
                    <p class="text-sm leading-relaxed text-bone/50">
                        Uniforms for a new office, a kit for a weekend league, or merch for an event — we quote every project individually.
                    </p>
                    <a href="{{ route('contact') }}" class="mt-5 inline-flex items-center gap-2 rounded-full bg-flame px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-flame-deep">
                        Ask us anything
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </div>
        </div>
    </section>

    {{-- Featured products --}}
    @if ($featuredProducts->isNotEmpty())
        <section class="border-y border-ink/10 bg-white/50">
            <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
                <div class="flex flex-col justify-between gap-6 sm:flex-row sm:items-end">
                    <x-section-heading
                        eyebrow="Customer favorites"
                        title="The pieces teams ask for most."
                        description="A starting point for your order. Names, numbers, colors and logos are all customisable."
                    />

                    <a href="{{ route('products', ['category' => 'athletic-jersey']) }}" class="group inline-flex shrink-0 items-center gap-2 text-sm font-semibold">
                        Shop jerseys
                        <span class="grid size-8 place-items-center rounded-full border border-ink/15 transition group-hover:border-flame group-hover:bg-flame group-hover:text-white">→</span>
                    </a>
                </div>

                <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach ($featuredProducts as $product)
                        <x-product-card :product="$product" :featured="true" wire:key="featured-{{ $product->id }}" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Process --}}
    <section id="process" class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
        <x-section-heading
            eyebrow="How it works"
            title="From first message to final delivery."
            description="A simple, transparent process — you always know what happens next."
        />

        <div class="mt-12 grid gap-5 md:grid-cols-3">
            @foreach ([
                ['title' => 'Tell us what you need', 'body' => 'Send your sport, quantity, sizes and any design references. We reply with a quote and suggestions.'],
                ['title' => 'Approve the design', 'body' => 'We prepare a proof with your colors, names, numbers and logos. Nothing goes to print until you say yes.'],
                ['title' => 'We make and deliver', 'body' => 'Production starts, we keep you updated, then your order ships to your address or team pickup point.'],
            ] as $item)
                <div class="group relative overflow-hidden rounded-3xl border border-ink/10 bg-white p-6 transition hover:border-ink/25 sm:p-7">
                    <h3 class="text-xl font-semibold tracking-[-0.02em]">{{ $item['title'] }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-ink/55">{{ $item['body'] }}</p>
                </div>
            @endforeach
        </div>
    </section>

    {{-- Capabilities --}}
    <section id="capabilities" class="border-y border-ink/10 bg-white/50">
        <div class="mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-2 lg:gap-16">
            <div class="relative order-2 lg:order-1">
                <div class="overflow-hidden rounded-[2rem] bg-sand">
                    <img src="{{ asset('images/brand/about-rack.jpg') }}" alt="Finished garments hanging on a rack" class="aspect-[4/3] w-full object-cover">
                </div>

                <div class="absolute -bottom-6 right-4 max-w-[15rem] rounded-2xl bg-ink p-4 text-bone shadow-[0_20px_50px_-25px_rgba(11,12,14,0.8)] sm:right-8">
                    <p class="text-sm font-semibold text-bone">In-house finishing</p>
                    <p class="mt-2 text-sm leading-snug text-bone/75">Printing, embroidery and quality checks under one roof.</p>
                </div>
            </div>

            <div class="order-1 lg:order-2">
                <x-section-heading
                    eyebrow="Built to last"
                    title="Good gear is in the details."
                    description="We choose fabrics for how they behave after ten washes, not just how they look in a photo. Then we finish every piece with the right decoration method."
                />

                <ul class="mt-8 grid gap-4 sm:grid-cols-2">
                    @foreach ([
                        'Full sublimation printing',
                        'Embroidery and patches',
                        'Screen print and DTF',
                        'Names, numbers and logos',
                        'Custom size runs',
                        'Rush orders on request',
                    ] as $feature)
                        <li class="flex items-center gap-3 text-sm text-ink/70">
                            <span class="grid size-5 shrink-0 place-items-center rounded-full bg-flame/10 text-flame">
                                <svg class="size-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                    <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                                </svg>
                            </span>
                            {{ $feature }}
                        </li>
                    @endforeach
                </ul>

                <a href="{{ route('about') }}" class="group mt-8 inline-flex items-center gap-2 text-sm font-semibold">
                    More about Yamban
                    <span class="transition-transform duration-300 group-hover:translate-x-1">→</span>
                </a>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
        <div class="relative overflow-hidden rounded-[2rem] bg-ink px-6 py-12 text-bone sm:px-12 sm:py-16">
            <div class="pointer-events-none absolute -right-24 -top-24 size-80 rounded-full bg-flame/20 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-32 -left-20 size-80 rounded-full bg-flame/10 blur-3xl"></div>

            <div class="relative grid gap-10 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div>
                    <h2 class="max-w-xl text-3xl font-semibold tracking-[-0.035em] text-balance sm:text-4xl lg:text-5xl">
                        Let's put your name on something worth wearing.
                    </h2>
                    <p class="mt-4 max-w-lg text-sm leading-relaxed text-bone/55 sm:text-base">
                        Send an inquiry with your quantity and deadline, or message the Facebook page directly. We'll reply with pricing and next steps.
                    </p>
                </div>

                <div class="flex flex-col gap-3 sm:flex-row lg:flex-col lg:items-stretch">
                    <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-flame px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-flame-deep">
                        Start an inquiry
                        <span aria-hidden="true">→</span>
                    </a>
                    <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full border border-bone/25 px-6 py-3.5 text-sm font-semibold transition hover:border-bone/60">
                        Message on Messenger
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
