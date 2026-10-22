<x-layouts.public
    title="Contact"
    description="Contact Yamban Enterprise for custom jerseys, corporate uniforms, riding longsleeves, hoodies and jackets. Send an inquiry by email or message us on Facebook."
>
    <section class="relative overflow-hidden border-b border-ink/10">
        <div class="pointer-events-none absolute -left-40 -top-32 size-[28rem] rounded-full bg-flame/10 blur-3xl"></div>

        <div class="relative mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
            <div class="grid gap-8 lg:grid-cols-[1.2fr_0.8fr] lg:items-end">
                <div>
                    <p class="label-xs flex items-center gap-3 text-flame">
                        <span class="h-px w-8 bg-flame"></span>
                        Contact
                    </p>

                    <h1 class="mt-6 text-[2.7rem] font-semibold leading-[0.95] tracking-[-0.045em] text-balance sm:text-5xl lg:text-[4rem]">
                        Let's talk gear.
                    </h1>

                    <p class="mt-5 max-w-xl text-base leading-relaxed text-ink/60">
                        Tell us what you're planning — a team kit, a company uniform rollout, or merch for an event. Send the details below and we'll reply with pricing and next steps.
                    </p>
                </div>

                <div class="flex flex-wrap gap-3 lg:justify-end">
                    <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full border border-ink/15 bg-white px-5 py-3 text-sm font-semibold transition hover:border-ink">
                        <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.5 21v-7h2.4l.4-2.9h-2.8V9.2c0-.8.2-1.4 1.4-1.4h1.5V5.2c-.3 0-1.2-.1-2.2-.1-2.2 0-3.7 1.3-3.7 3.8v2.2H8v2.9h2.5v7h3Z" />
                        </svg>
                        Facebook page
                    </a>

                    <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full border border-ink/15 bg-white px-5 py-3 text-sm font-semibold transition hover:border-ink">
                        Messenger
                    </a>
                </div>
            </div>

            @if ($product)
                <div class="mt-10 flex flex-col gap-4 rounded-2xl border border-ink/10 bg-white p-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-center gap-4">
                        <img src="{{ $product->imageUrl() }}" alt="{{ $product->name }}" class="size-14 rounded-xl object-cover">
                        <div>
                            <p class="label-xs text-flame">Inquiring about</p>
                            <p class="mt-1.5 text-sm font-semibold">{{ $product->name }}</p>
                            <p class="text-xs text-ink/45">{{ $product->category?->name }}@if ($product->price_from) · From {{ $product->price_from }} @endif</p>
                        </div>
                    </div>

                    <a href="{{ route('products', ['category' => $product->category?->slug]) }}" class="text-xs font-semibold text-ink/50 underline-offset-4 hover:text-ink hover:underline">
                        View similar products
                    </a>
                </div>
            @endif
        </div>
    </section>

    <section class="mx-auto max-w-7xl px-5 py-12 sm:px-8 sm:py-16">
        <div class="grid gap-8 lg:grid-cols-[1.15fr_0.85fr] lg:gap-12">
            <div>
                <livewire:inquiry-form />
            </div>

            <aside class="space-y-4">
                <div class="rounded-3xl bg-ink p-6 text-bone sm:p-7">
                    <p class="label-xs text-flame">Direct line</p>

                    <div class="mt-6 space-y-5">
                        <div>
                            <p class="text-[10px] font-medium tracking-[0.16em] text-bone/40 uppercase">Email</p>
                            <a href="mailto:{{ config('site.email') }}" class="mt-1.5 block text-lg font-semibold break-all transition hover:text-flame">{{ config('site.email') }}</a>
                        </div>

                        @if (config('site.phone'))
                            <div>
                                <p class="text-[10px] font-medium tracking-[0.16em] text-bone/40 uppercase">Phone</p>
                                <a href="tel:{{ config('site.phone') }}" class="mt-1.5 block text-lg font-semibold transition hover:text-flame">{{ config('site.phone') }}</a>
                            </div>
                        @endif

                        <div>
                            <p class="text-[10px] font-medium tracking-[0.16em] text-bone/40 uppercase">Facebook</p>
                            <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="mt-1.5 block text-lg font-semibold transition hover:text-flame">facebook.com/yambanEnterprise</a>
                        </div>

                        <div>
                            <p class="text-[10px] font-medium tracking-[0.16em] text-bone/40 uppercase">Hours</p>
                            <p class="mt-1.5 text-sm text-bone/75">{{ config('site.hours') }}</p>
                        </div>

                        <div>
                            <p class="text-[10px] font-medium tracking-[0.16em] text-bone/40 uppercase">Coverage</p>
                            <p class="mt-1.5 text-sm text-bone/75">{{ config('site.address') }}</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-7">
                    <p class="label-xs text-flame">Before you send</p>
                    <h2 class="mt-3 text-lg font-semibold tracking-[-0.02em]">What helps us quote faster</h2>

                    <ul class="mt-4 space-y-3 text-sm text-ink/60">
                        @foreach ([
                            'The product and quantity you have in mind',
                            'Your sizes or a rough size breakdown',
                            'Design references, colors or existing logos',
                            'Your target deadline and delivery location',
                        ] as $tip)
                            <li class="flex gap-3">
                                <span class="mt-1.5 size-1.5 shrink-0 rounded-full bg-flame"></span>
                                {{ $tip }}
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="rounded-3xl bg-flame/10 p-6 sm:p-7">
                    <p class="text-sm font-semibold">Prefer to chat?</p>
                    <p class="mt-2 text-sm leading-relaxed text-ink/60">Message the Facebook page and send photos of what you have in mind. It's often the fastest way to get a feel for pricing.</p>
                    <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-flame-deep hover:underline">
                        Open Messenger
                        <span aria-hidden="true">→</span>
                    </a>
                </div>
            </aside>
        </div>
    </section>

    <section class="border-t border-ink/10 bg-white/50">
        <div class="mx-auto max-w-7xl px-5 py-14 sm:px-8 sm:py-20">
            <div class="grid gap-10 lg:grid-cols-[0.8fr_1.2fr] lg:gap-16">
                <x-section-heading
                    eyebrow="Common questions"
                    title="A few things worth knowing."
                />

                <div class="divide-y divide-ink/10 border-y border-ink/10">
                    @foreach ([
                        ['q' => 'What is the minimum order?', 'a' => 'Most styles start at 10–20 pieces, and some jackets and hoodies can go as low as 5. Message us with your quantity and we will tell you what is possible.'],
                        ['q' => 'How long does an order take?', 'a' => 'Production usually takes 7–15 working days after the design is approved. Rush orders can sometimes be arranged — ask early and we will be honest about the timeline.'],
                        ['q' => 'Can you match our existing colors and logo?', 'a' => 'Yes. Send your brand colors (hex codes if you have them), logo files, or even a photo of an old uniform. We will prepare a proof for approval before anything is printed.'],
                        ['q' => 'Do you deliver outside your area?', 'a' => 'Yes, we ship nationwide through courier partners. Delivery fees depend on the destination and order size, and we will include them in your quote.'],
                    ] as $faq)
                        <details class="group py-5">
                            <summary class="flex cursor-pointer list-none items-center justify-between gap-4 text-left text-base font-semibold">
                                {{ $faq['q'] }}
                                <span class="grid size-7 shrink-0 place-items-center rounded-full border border-ink/15 text-sm transition group-open:rotate-45 group-open:border-flame group-open:bg-flame group-open:text-white">+</span>
                            </summary>
                            <p class="mt-3 max-w-2xl pr-10 text-sm leading-relaxed text-ink/55">{{ $faq['a'] }}</p>
                        </details>
                    @endforeach
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
