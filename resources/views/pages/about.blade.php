<x-layouts.public
    title="About"
    description="Yamban Enterprise is a Philippine custom apparel maker producing athletic jerseys, corporate uniforms, riding longsleeves, hoodies and jackets for teams, schools and businesses."
>
    {{-- Hero --}}
    <section class="relative overflow-hidden border-b border-ink/10">
        <div class="mx-auto grid max-w-7xl gap-10 px-5 py-14 sm:px-8 sm:py-20 lg:grid-cols-[1.1fr_0.9fr] lg:items-center lg:gap-16">
            <div>
                <p class="label-xs flex items-center gap-3 text-flame">
                    <span class="h-px w-8 bg-flame"></span>
                    About Yamban Enterprise
                </p>

                <h1 class="mt-6 text-[2.6rem] font-semibold leading-[0.96] tracking-[-0.045em] text-balance sm:text-5xl lg:text-[4rem]">
                    We make the gear your team remembers.
                </h1>

                <p class="mt-6 max-w-xl text-base leading-relaxed text-ink/60 sm:text-lg">
                    Yamban Enterprise is a Philippine apparel workshop focused on one thing: turning a team's identity into clothing people actually want to wear — on the court, in the office, and on the road.
                </p>
            </div>

            <div class="relative">
                <div class="overflow-hidden rounded-[2rem] bg-sand">
                    <img src="{{ asset('images/brand/about-team.jpg') }}" alt="A football team huddled on the pitch" class="aspect-[4/3] w-full object-cover">
                </div>

                <div class="absolute -bottom-5 left-4 rounded-2xl border border-ink/10 bg-bone px-5 py-4 shadow-[0_18px_40px_-24px_rgba(11,12,14,0.5)] sm:left-8">
                    <p class="label-xs text-ink/40">What drives us</p>
                    <p class="mt-2 max-w-[14rem] text-sm font-semibold leading-snug">Gear that looks right, fits right and holds up.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Story --}}
    <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-20">
            <div>
                <p class="label-xs text-flame">Our story</p>
                <h2 class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-balance sm:text-4xl">
                    Built around teams, not catalogs.
                </h2>
            </div>

            <div class="space-y-5 text-base leading-relaxed text-ink/60">
                <p>
                    Yamban Enterprise started with a practical problem: local teams and small businesses could find shirts, but not gear that felt like theirs. Sizes were inconsistent, designs were generic, and there was no one to talk to when something needed to change.
                </p>
                <p>
                    So we built the business around the conversation instead. You tell us the sport, the team, the colors and the deadline. We help shape the design, prepare a proof, and produce the order in the quantities that make sense for you — from a single squad to a full company rollout.
                </p>
                <p>
                    Today we produce athletic jerseys, corporate uniforms, riding longsleeves, hoodies and jackets for teams, schools, offices and events across the Philippines. Every order still goes through the same process: a real person, a clear quote, and a proof before production.
                </p>
            </div>
        </div>
    </section>

    {{-- Values --}}
    <section class="border-y border-ink/10 bg-white/50">
        <div class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
            <x-section-heading
                eyebrow="What we care about"
                title="Three things we won't compromise on."
            />

            <div class="mt-10 grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['title' => 'Made for movement', 'body' => 'Fabrics are chosen for how they breathe, stretch and recover — not just how they look on a hanger. Fit notes come from real teams, not a size chart alone.'],
                    ['title' => 'Printed to last', 'body' => 'We match the decoration to the garment: sublimation for full-color kits, embroidery for uniforms, and durable transfers where they make sense.'],
                    ['title' => 'People first', 'body' => 'One point of contact from quote to delivery. You get straight answers about pricing, lead times and what is actually possible.'],
                ] as $value)
                    <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-7">
                        <span class="grid size-10 place-items-center rounded-full bg-flame/10 text-flame">
                            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                                <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                        </span>
                        <h3 class="mt-6 text-xl font-semibold tracking-[-0.02em]">{{ $value['title'] }}</h3>
                        <p class="mt-3 text-sm leading-relaxed text-ink/55">{{ $value['body'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- What we make --}}
    <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-24">
        <div class="grid gap-10 lg:grid-cols-[0.9fr_1.1fr] lg:gap-16">
            <div>
                <p class="label-xs text-flame">What we make</p>
                <h2 class="mt-4 text-3xl font-semibold tracking-[-0.035em] text-balance sm:text-4xl">
                    A focused range, made properly.
                </h2>
                <p class="mt-4 max-w-md text-sm leading-relaxed text-ink/55">
                    We keep the range tight so we can get the details right. If your project doesn't fit neatly into one of these categories, ask — we take on custom pieces too.
                </p>

                <a href="{{ route('products') }}" class="group mt-7 inline-flex items-center gap-2 text-sm font-semibold">
                    See the full catalog
                    <span class="grid size-8 place-items-center rounded-full border border-ink/15 transition group-hover:border-flame group-hover:bg-flame group-hover:text-white">→</span>
                </a>

                <div class="mt-10 hidden overflow-hidden rounded-2xl bg-sand lg:block">
                    <img src="{{ asset('images/brand/about-rack.jpg') }}" alt="Garments hanging on a rack in the workshop" class="aspect-[4/3] w-full object-cover">
                </div>
            </div>

            <div class="divide-y divide-ink/10 border-y border-ink/10">
                @foreach ($categories as $category)
                    <a href="{{ route('products', ['category' => $category->slug]) }}" class="group flex items-start gap-5 py-6 transition">
                        <span class="mt-1 text-xs font-semibold text-ink/30">0{{ $loop->iteration }}</span>

                        <div class="flex-1">
                            <div class="flex flex-wrap items-baseline justify-between gap-2">
                                <h3 class="text-xl font-semibold tracking-[-0.02em] transition group-hover:text-flame">{{ $category->name }}</h3>
                                <span class="text-[11px] tracking-[0.12em] text-ink/35 uppercase">{{ $category->products_count }} {{ Str::plural('style', $category->products_count) }}</span>
                            </div>
                            <p class="mt-2 max-w-xl text-sm leading-relaxed text-ink/55">{{ $category->description }}</p>
                        </div>

                        <span class="mt-1 text-ink/25 transition group-hover:translate-x-1 group-hover:text-flame">→</span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="border-t border-ink/10 bg-ink text-bone">
        <div class="mx-auto flex max-w-7xl flex-col justify-between gap-8 px-5 py-14 sm:px-8 sm:py-20 lg:flex-row lg:items-center">
            <div>
                <p class="label-xs text-flame">Let's make something</p>
                <h2 class="mt-4 max-w-2xl text-3xl font-semibold tracking-[-0.035em] text-balance sm:text-4xl">
                    Tell us about your team, your office or your next event.
                </h2>
            </div>

            <div class="flex flex-col gap-3 sm:flex-row">
                <a href="{{ route('contact') }}" class="inline-flex items-center justify-center gap-2 rounded-full bg-flame px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-flame-deep">
                    Get a quote
                    <span aria-hidden="true">→</span>
                </a>
                <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center justify-center gap-2 rounded-full border border-bone/25 px-6 py-3.5 text-sm font-semibold transition hover:border-bone/60">
                    Visit our Facebook
                </a>
            </div>
        </div>
    </section>
</x-layouts.public>
