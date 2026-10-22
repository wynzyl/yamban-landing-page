<?php

use App\Models\Category;
use App\Models\Product;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Url;
use Livewire\Component;

new class extends Component {
    #[Url(as: 'category', history: true)]
    public string $category = 'all';

    #[Url(as: 'q', history: true)]
    public string $search = '';

    /**
     * Visible categories with their product counts.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()
            ->active()
            ->ordered()
            ->withCount(['products' => fn ($query) => $query->active()])
            ->get();
    }

    /**
     * Products matching the active filters.
     */
    #[Computed]
    public function products()
    {
        return Product::query()
            ->active()
            ->ordered()
            ->with('category')
            ->when(
                $this->category !== 'all' && $this->categories->contains('slug', $this->category),
                fn ($query) => $query->whereHas('category', fn ($category) => $category->where('slug', $this->category)),
            )
            ->when(
                filled($this->search),
                fn ($query) => $query->where(function ($query) {
                    $query->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%')
                        ->orWhereHas('category', fn ($category) => $category->where('name', 'like', '%'.$this->search.'%'));
                }),
            )
            ->get();
    }

    /**
     * Reset every filter back to the default view.
     */
    public function clearFilters(): void
    {
        $this->category = 'all';
        $this->search = '';
    }
};
?>

<div>
    <div class="flex flex-col gap-5 border-y border-ink/10 py-5 lg:flex-row lg:items-center lg:justify-between">
        <div class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 [scrollbar-width:none] [&::-webkit-scrollbar]:hidden">
            <button
                type="button"
                wire:click="$set('category', 'all')"
                @class([
                    'shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition',
                    'border-ink bg-ink text-bone' => $category === 'all',
                    'border-ink/15 text-ink/60 hover:border-ink/40 hover:text-ink' => $category !== 'all',
                ])
            >
                All products
                <span @class(['ml-1 text-xs', 'text-bone/50' => $category === 'all', 'text-ink/35' => $category !== 'all'])>
                    {{ $this->categories->sum('products_count') }}
                </span>
            </button>

            @foreach ($this->categories as $item)
                <button
                    type="button"
                    wire:key="category-filter-{{ $item->id }}"
                    wire:click="$set('category', '{{ $item->slug }}')"
                    @class([
                        'shrink-0 rounded-full border px-4 py-2 text-sm font-medium transition',
                        'border-ink bg-ink text-bone' => $category === $item->slug,
                        'border-ink/15 text-ink/60 hover:border-ink/40 hover:text-ink' => $category !== $item->slug,
                    ])
                >
                    {{ $item->name }}
                    <span @class(['ml-1 text-xs', 'text-bone/50' => $category === $item->slug, 'text-ink/35' => $category !== $item->slug])>
                        {{ $item->products_count }}
                    </span>
                </button>
            @endforeach
        </div>

        <div class="relative w-full lg:w-72">
            <svg class="pointer-events-none absolute left-4 top-1/2 size-4 -translate-y-1/2 text-ink/35" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3.5-3.5" stroke-linecap="round" />
            </svg>

            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                placeholder="Search jerseys, polos, hoodies…"
                aria-label="Search products"
                class="w-full rounded-full border border-ink/15 bg-white py-2.5 pl-11 pr-4 text-sm text-ink placeholder:text-ink/35 focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
            >
        </div>
    </div>

    <div class="mt-6 flex items-center justify-between gap-4">
        <p class="text-sm text-ink/50" aria-live="polite">
            Showing <span class="font-semibold text-ink">{{ $this->products->count() }}</span>
            {{ Str::plural('product', $this->products->count()) }}

            @if ($category !== 'all')
                in <span class="font-semibold text-ink">{{ $this->categories->firstWhere('slug', $category)?->name }}</span>
            @endif

            @if (filled($search))
                matching “{{ $search }}”
            @endif
        </p>

        @if ($category !== 'all' || filled($search))
            <button type="button" wire:click="clearFilters" class="text-sm font-medium text-flame underline-offset-4 hover:underline">
                Clear filters
            </button>
        @endif
    </div>

    <div class="mt-6" wire:loading.class="opacity-50" wire:target="category,search">
        @if ($this->products->isEmpty())
            <div class="rounded-3xl border border-dashed border-ink/20 bg-white/60 px-6 py-20 text-center">
                <p class="label-xs text-flame">Nothing here yet</p>
                <h3 class="mt-3 text-2xl font-semibold tracking-[-0.03em]">No products match that search.</h3>
                <p class="mx-auto mt-2 max-w-md text-sm text-ink/55">
                    Try another category or clear the filters. We also make custom pieces that aren't listed — just tell us what you need.
                </p>
                <button type="button" wire:click="clearFilters" class="mt-6 inline-flex items-center gap-2 rounded-full bg-ink px-5 py-2.5 text-sm font-semibold text-bone transition hover:bg-ink-soft">
                    Show all products
                </button>
            </div>
        @else
            <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($this->products as $product)
                    <div wire:key="product-{{ $product->id }}" class="contents">
                        <x-product-card :product="$product" :featured="$product->is_featured" />
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-12 flex flex-col items-start justify-between gap-5 rounded-3xl bg-ink px-6 py-8 text-bone sm:flex-row sm:items-center sm:px-8">
        <div>
            <p class="label-xs text-flame">Can't find it?</p>
            <p class="mt-3 max-w-lg text-xl font-semibold tracking-[-0.02em] text-balance">
                We produce custom pieces beyond this catalog — send us a reference and we'll quote it.
            </p>
        </div>

        <a href="{{ route('contact') }}" class="shrink-0 rounded-full bg-flame px-5 py-3 text-sm font-semibold text-white transition hover:bg-flame-deep">
            Ask for a quote →
        </a>
    </div>
</div>
