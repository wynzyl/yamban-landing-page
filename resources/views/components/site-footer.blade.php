@php
    $footerCategories = \App\Models\Category::query()->active()->ordered()->get(['name', 'slug']);
    $year = now()->year;
@endphp

<footer class="border-t border-bone/10 bg-ink text-bone">
    <div class="mx-auto max-w-7xl px-5 pt-16 sm:px-8 sm:pt-20">
        <div class="grid gap-12 border-b border-bone/15 pb-14 lg:grid-cols-[1.4fr_1fr_1fr] lg:gap-16">
            <div>
                <p class="label-xs text-flame">Let's build your next kit</p>
                <h2 class="mt-4 max-w-md text-3xl font-semibold tracking-[-0.03em] text-balance sm:text-4xl">
                    Tell us what your team needs. We'll handle the rest.
                </h2>

                <div class="mt-7 flex flex-wrap gap-3">
                    <a href="{{ route('contact') }}" class="group inline-flex items-center gap-2 rounded-full bg-flame px-5 py-3 text-sm font-semibold text-white transition hover:bg-flame-deep">
                        Start an inquiry
                        <span class="transition-transform duration-300 group-hover:translate-x-0.5">→</span>
                    </a>

                    <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full border border-bone/25 px-5 py-3 text-sm font-semibold text-bone transition hover:border-bone/60">
                        Chat on Messenger
                    </a>
                </div>
            </div>

            <div>
                <p class="label-xs text-bone/40">Explore</p>
                <ul class="mt-5 space-y-3 text-sm">
                    <li><a href="{{ route('home') }}" class="text-bone/75 transition hover:text-white">Home</a></li>
                    <li><a href="{{ route('about') }}" class="text-bone/75 transition hover:text-white">About</a></li>
                    <li><a href="{{ route('products') }}" class="text-bone/75 transition hover:text-white">Products</a></li>
                    <li><a href="{{ route('designs') }}" class="text-bone/75 transition hover:text-white">Designs</a></li>
                    <li><a href="{{ route('contact') }}" class="text-bone/75 transition hover:text-white">Contact</a></li>
                    <li><a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="text-bone/75 transition hover:text-white">Facebook page</a></li>
                </ul>
            </div>

            <div>
                <p class="label-xs text-bone/40">Categories</p>
                <ul class="mt-5 space-y-3 text-sm">
                    @foreach ($footerCategories as $category)
                        <li>
                            <a href="{{ route('products', ['category' => $category->slug]) }}" class="text-bone/75 transition hover:text-white">
                                {{ $category->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="grid gap-8 py-10 text-sm sm:grid-cols-3">
            <div>
                <p class="label-xs text-bone/40">Email</p>
                <a href="mailto:{{ config('site.email') }}" class="mt-2 block text-bone/85 transition hover:text-white">{{ config('site.email') }}</a>
            </div>

            <div>
                <p class="label-xs text-bone/40">Where we are</p>
                <p class="mt-2 text-bone/85">{{ config('site.address') }}</p>
                <p class="mt-1 text-bone/45">{{ config('site.hours') }}</p>
            </div>

            <div>
                <p class="label-xs text-bone/40">Follow</p>
                <div class="mt-2 flex gap-4">
                    <a href="{{ config('site.facebook') }}" target="_blank" rel="noopener noreferrer" class="text-bone/85 transition hover:text-white">Facebook</a>
                    <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="text-bone/85 transition hover:text-white">Messenger</a>
                </div>
            </div>
        </div>

        <div class="flex flex-col gap-3 border-t border-bone/15 py-7 text-xs text-bone/40 sm:flex-row sm:items-center sm:justify-between">
            <p>© {{ $year }} {{ config('site.name') }}. All rights reserved.</p>
            <p>Sportswear · Corporate uniforms · Team gear</p>
        </div>
    </div>
</footer>
