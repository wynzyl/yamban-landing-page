<?php

use App\Mail\NewInquiry;
use App\Models\Category;
use App\Models\Inquiry;
use App\Models\Product;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Component;

new class extends Component {
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public string $company = '';

    public ?int $categoryId = null;

    public ?int $productId = null;

    public ?int $quantity = null;

    public string $message = '';

    /**
     * Honeypot field. Real customers never see it, bots fill it in.
     */
    public string $website = '';

    public bool $submitted = false;

    public ?string $reference = null;

    /**
     * Prefill the form from the product or category the visitor clicked.
     */
    public function mount(): void
    {
        $product = Product::query()
            ->active()
            ->where('slug', request()->query('product'))
            ->first();

        if ($product) {
            $this->productId = $product->id;
            $this->categoryId = $product->category_id;

            return;
        }

        $category = Category::query()
            ->active()
            ->where('slug', request()->query('category'))
            ->first();

        if ($category) {
            $this->categoryId = $category->id;
        }
    }

    /**
     * Categories available in the inquiry form.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()->active()->ordered()->get(['id', 'name', 'slug']);
    }

    /**
     * Products that belong to the selected category.
     */
    #[Computed]
    public function products()
    {
        if (! $this->categoryId) {
            return collect();
        }

        return Product::query()
            ->active()
            ->ordered()
            ->where('category_id', $this->categoryId)
            ->get(['id', 'name', 'category_id']);
    }

    /**
     * Reset the selected product when the category changes.
     */
    public function updatedCategoryId(): void
    {
        $this->productId = null;
        unset($this->products);
    }

    /**
     * Store the inquiry and notify the team by email.
     */
    public function submit(): void
    {
        if (filled($this->website)) {
            $this->submitted = true;

            return;
        }

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:180'],
            'phone' => ['nullable', 'string', 'max:40'],
            'company' => ['nullable', 'string', 'max:120'],
            'categoryId' => ['nullable', 'integer', Rule::exists('categories', 'id')->where('is_active', true)],
            'productId' => ['nullable', 'integer', Rule::exists('products', 'id')->where('is_active', true)],
            'quantity' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'message' => ['required', 'string', 'min:10', 'max:4000'],
        ], [
            'name.required' => 'Please tell us your name.',
            'email.required' => 'We need an email address to reply to.',
            'message.required' => 'Tell us a little about what you need.',
            'message.min' => 'Add a few more details so we can quote accurately.',
        ]);

        $inquiry = Inquiry::query()->create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?: null,
            'company' => $validated['company'] ?: null,
            'category_id' => $validated['categoryId'] ?: null,
            'product_id' => $validated['productId'] ?: null,
            'quantity' => $validated['quantity'] ?: null,
            'message' => $validated['message'],
            'status' => 'new',
            'ip_address' => request()->ip(),
        ]);

        try {
            Mail::to(config('site.email'))->send(new NewInquiry($inquiry));
        } catch (Throwable $exception) {
            Log::error('Unable to send inquiry notification email.', [
                'inquiry_id' => $inquiry->id,
                'exception' => $exception,
            ]);
        }

        $this->reference = 'YBE-'.str_pad((string) $inquiry->id, 5, '0', STR_PAD_LEFT);
        $this->submitted = true;

        $this->reset(['name', 'email', 'phone', 'company', 'categoryId', 'productId', 'quantity', 'message', 'website']);
    }

    /**
     * Start another inquiry after a successful submission.
     */
    public function startAnother(): void
    {
        $this->submitted = false;
        $this->reference = null;
        $this->resetValidation();
    }
};
?>

<div>
    @if ($submitted)
        <div class="rounded-3xl border border-ink/10 bg-white p-8 sm:p-10">
            <div class="grid size-12 place-items-center rounded-full bg-flame/10 text-flame">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path d="m5 13 4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
                </svg>
            </div>

            <p class="label-xs mt-6 text-flame">Inquiry received</p>
            <h3 class="mt-3 text-2xl font-semibold tracking-[-0.03em] sm:text-3xl">Thanks — we're on it.</h3>
            <p class="mt-3 max-w-md text-sm leading-relaxed text-ink/60">
                Your inquiry is in our inbox. We usually reply within one business day. If it's urgent, message us on Facebook and mention your reference number.
            </p>

            @if ($reference)
                <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-bone px-4 py-2 text-sm">
                    <span class="text-ink/45">Reference</span>
                    <span class="font-semibold tracking-wide">{{ $reference }}</span>
                </p>
            @endif

            <div class="mt-8 flex flex-wrap gap-3">
                <a href="{{ config('site.messenger') }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-full bg-ink px-5 py-3 text-sm font-semibold text-bone transition hover:bg-ink-soft">
                    Message us on Messenger
                </a>
                <button type="button" wire:click="startAnother" class="inline-flex items-center gap-2 rounded-full border border-ink/15 px-5 py-3 text-sm font-semibold transition hover:border-ink">
                    Send another inquiry
                </button>
            </div>
        </div>
    @else
        <form wire:submit="submit" class="rounded-3xl border border-ink/10 bg-white p-6 sm:p-8">
            <div class="flex items-end justify-between gap-4">
                <div>
                    <p class="label-xs text-flame">Inquiry form</p>
                    <h3 class="mt-3 text-2xl font-semibold tracking-[-0.03em]">Tell us about your project</h3>
                </div>
                <p class="hidden text-xs text-ink/40 sm:block">* Required</p>
            </div>

            <div class="mt-7 grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="inquiry-name" class="mb-2 block text-sm font-medium">Name *</label>
                    <input
                        id="inquiry-name"
                        type="text"
                        wire:model="name"
                        autocomplete="name"
                        placeholder="Juan Dela Cruz"
                        @class([
                            'w-full rounded-xl border bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:outline-none focus:ring-2 focus:ring-ink/10',
                            'border-ink/15 focus:border-ink' => ! $errors->has('name'),
                            'border-red-400' => $errors->has('name'),
                        ])
                    >
                    @error('name') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-email" class="mb-2 block text-sm font-medium">Email *</label>
                    <input
                        id="inquiry-email"
                        type="email"
                        wire:model="email"
                        autocomplete="email"
                        placeholder="you@company.com"
                        @class([
                            'w-full rounded-xl border bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:outline-none focus:ring-2 focus:ring-ink/10',
                            'border-ink/15 focus:border-ink' => ! $errors->has('email'),
                            'border-red-400' => $errors->has('email'),
                        ])
                    >
                    @error('email') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-phone" class="mb-2 block text-sm font-medium">Phone / Viber</label>
                    <input
                        id="inquiry-phone"
                        type="tel"
                        wire:model="phone"
                        autocomplete="tel"
                        placeholder="0917 000 0000"
                        class="w-full rounded-xl border border-ink/15 bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                    >
                    @error('phone') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-company" class="mb-2 block text-sm font-medium">Team / Company</label>
                    <input
                        id="inquiry-company"
                        type="text"
                        wire:model="company"
                        autocomplete="organization"
                        placeholder="Team name or business"
                        class="w-full rounded-xl border border-ink/15 bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                    >
                    @error('company') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-category" class="mb-2 block text-sm font-medium">Category</label>
                    <select
                        id="inquiry-category"
                        wire:model.live="categoryId"
                        class="w-full rounded-xl border border-ink/15 bg-bone/60 px-4 py-3 text-sm focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                    >
                        <option value="">Choose a category</option>
                        @foreach ($this->categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    @error('categoryId') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-product" class="mb-2 block text-sm font-medium">Product</label>
                    <select
                        id="inquiry-product"
                        wire:model="productId"
                        @disabled($this->products->isEmpty())
                        class="w-full rounded-xl border border-ink/15 bg-bone/60 px-4 py-3 text-sm focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10 disabled:cursor-not-allowed disabled:opacity-50"
                    >
                        <option value="">{{ $this->products->isEmpty() ? 'Choose a category first' : 'Any product' }}</option>
                        @foreach ($this->products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('productId') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="inquiry-quantity" class="mb-2 block text-sm font-medium">Estimated quantity</label>
                    <input
                        id="inquiry-quantity"
                        type="number"
                        min="1"
                        wire:model="quantity"
                        placeholder="e.g. 25"
                        class="w-full rounded-xl border border-ink/15 bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:border-ink focus:outline-none focus:ring-2 focus:ring-ink/10"
                    >
                    @error('quantity') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="hidden" aria-hidden="true">
                    <label for="inquiry-website">Website</label>
                    <input id="inquiry-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
                </div>

                <div class="sm:col-span-2">
                    <label for="inquiry-message" class="mb-2 block text-sm font-medium">What do you need? *</label>
                    <textarea
                        id="inquiry-message"
                        wire:model="message"
                        rows="5"
                        placeholder="Design idea, sizes, deadline, delivery location — anything that helps us quote accurately."
                        @class([
                            'w-full rounded-xl border bg-bone/60 px-4 py-3 text-sm placeholder:text-ink/30 focus:outline-none focus:ring-2 focus:ring-ink/10',
                            'border-ink/15 focus:border-ink' => ! $errors->has('message'),
                            'border-red-400' => $errors->has('message'),
                        ])
                    ></textarea>
                    @error('message') <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="mt-7 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <p class="text-xs leading-relaxed text-ink/45">
                    We use your details only to answer this inquiry.
                </p>

                <button
                    type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-full bg-flame px-6 py-3.5 text-sm font-semibold text-white transition hover:bg-flame-deep disabled:opacity-60"
                    wire:loading.attr="disabled"
                    wire:target="submit"
                >
                    <span wire:loading.remove wire:target="submit">Send inquiry</span>
                    <span wire:loading wire:target="submit">Sending…</span>
                    <span aria-hidden="true">→</span>
                </button>
            </div>
        </form>
    @endif
</div>
