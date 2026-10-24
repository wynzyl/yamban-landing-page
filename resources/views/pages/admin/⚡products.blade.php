<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Support\ProductImageStore;
use Flux\Flux;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

new #[Title('Manage products')] class extends Component {
    use WithFileUploads;

    public ?int $editingId = null;

    public string $filterCategory = 'all';

    public ?int $category_id = null;
    public string $name = '';
    public string $slug = '';
    public string $description = '';
    public string $features = '';
    public string $price_from = '';
    public string $minimum_order = '';
    public int $sort_order = 0;
    public bool $is_featured = false;
    public bool $is_active = true;

    /**
     * Newly selected photos, converted to WebP when the product is saved.
     *
     * @var array<int, \Livewire\Features\SupportFileUploads\TemporaryUploadedFile>
     */
    public array $photoUploads = [];

    /**
     * Temporary filenames already converted during this editing session.
     *
     * @var array<int, string>
     */
    public array $handledPhotoUploads = [];

    /**
     * Categories available for assignment and filtering.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()->ordered()->get();
    }

    /**
     * Photos already saved for the product being edited.
     */
    #[Computed]
    public function existingPhotos()
    {
        if (! $this->editingId) {
            return collect();
        }

        return ProductImage::query()
            ->where('product_id', $this->editingId)
            ->ordered()
            ->get();
    }

    /**
     * Products matching the category filter.
     */
    #[Computed]
    public function products()
    {
        return Product::query()
            ->with('category')
            ->withCount('images')
            ->when($this->filterCategory !== 'all', fn ($query) => $query->where('category_id', $this->filterCategory))
            ->orderBy('category_id')
            ->ordered()
            ->get();
    }

    /**
     * Open the form for a new product.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->category_id = $this->filterCategory !== 'all' ? (int) $this->filterCategory : $this->categories->first()?->id;

        Flux::modal('product-form')->show();
    }

    /**
     * Open the form pre-filled with an existing product.
     */
    public function edit(int $id): void
    {
        $product = Product::query()->findOrFail($id);

        $this->resetValidation();
        $this->editingId = $product->id;
        $this->category_id = $product->category_id;
        $this->name = $product->name;
        $this->slug = $product->slug;
        $this->description = (string) $product->description;
        $this->features = implode("\n", $product->features ?? []);
        $this->price_from = (string) $product->price_from;
        $this->minimum_order = (string) $product->minimum_order;
        $this->sort_order = $product->sort_order;
        $this->is_featured = $product->is_featured;
        $this->is_active = $product->is_active;
        $this->photoUploads = [];
        $this->handledPhotoUploads = [];

        Flux::modal('product-form')->show();
    }

    /**
     * Convert and attach photos as soon as they finish uploading.
     *
     * Photos for an existing product are stored immediately so they are never
     * lost when the form is closed without pressing Save.
     */
    public function updatedPhotoUploads(): void
    {
        if ($this->editingId === null || $this->photoUploads === []) {
            return;
        }

        $this->validateOnly('photoUploads.*', [
            'photoUploads.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
        ]);

        $product = Product::query()->findOrFail($this->editingId);
        $store = app(ProductImageStore::class);
        $stored = false;

        foreach ($this->photoUploads as $upload) {
            if (in_array($upload->getFilename(), $this->handledPhotoUploads, true)) {
                continue;
            }

            $store->store($product, $upload);
            $this->handledPhotoUploads[] = $upload->getFilename();
            $stored = true;
        }

        if ($stored) {
            Flux::toast(variant: 'success', text: __('Photos saved.'));
        }

        $this->reset('photoUploads');
        unset($this->existingPhotos, $this->products);
    }

    /**
     * Create or update the product being edited.
     */
    public function save(): void
    {
        $this->slug = Str::slug(filled($this->slug) ? $this->slug : $this->name);

        $validated = $this->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('products', 'slug')->ignore($this->editingId)],
            'description' => ['nullable', 'string', 'max:5000'],
            'features' => ['nullable', 'string', 'max:2000'],
            'price_from' => ['nullable', 'string', 'max:255'],
            'minimum_order' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_featured' => ['boolean'],
            'is_active' => ['boolean'],
            'photoUploads' => ['array'],
            'photoUploads.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif,avif', 'max:5120'],
        ]);

        $photos = $validated['photoUploads'];
        $validated = Arr::except($validated, 'photoUploads');

        $validated['features'] = collect(preg_split('/\R/', $validated['features'] ?? ''))
            ->map(fn ($feature) => trim($feature))
            ->filter()
            ->values()
            ->all();

        $product = Product::query()->updateOrCreate(['id' => $this->editingId], $validated);

        $store = app(ProductImageStore::class);

        foreach ($photos as $photo) {
            $store->store($product, $photo);
        }

        Flux::modal('product-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? __('Product updated.') : __('Product created.'));

        $this->resetForm();
        unset($this->products, $this->existingPhotos);
    }

    /**
     * Delete a photo from the product being edited.
     */
    public function deletePhoto(int $id): void
    {
        $photo = ProductImage::query()->findOrFail($id);

        abort_unless($photo->product_id === $this->editingId, 403);

        $photo->deleteFile();

        Flux::toast(variant: 'success', text: __('Photo deleted.'));

        unset($this->existingPhotos, $this->products);
    }

    /**
     * Show or hide the product on the storefront.
     */
    public function toggleActive(int $id): void
    {
        $product = Product::query()->findOrFail($id);
        $product->update(['is_active' => ! $product->is_active]);

        unset($this->products);
    }

    /**
     * Delete a product.
     */
    public function delete(int $id): void
    {
        Product::query()->findOrFail($id)->delete();

        Flux::toast(variant: 'success', text: __('Product deleted.'));

        unset($this->products);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'category_id', 'name', 'slug', 'description', 'features', 'price_from', 'minimum_order', 'sort_order', 'is_featured', 'is_active', 'photoUploads', 'handledPhotoUploads');
    }
};
?>

<section class="w-full">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Products') }}</flux:heading>
            <flux:subheading>{{ __('Manage the products shown in the catalog.') }}</flux:subheading>
        </div>

        <div class="flex items-center gap-3">
            <flux:select wire:model.live="filterCategory" class="min-w-48" :aria-label="__('Filter by category')">
                <flux:select.option value="all">{{ __('All categories') }}</flux:select.option>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="primary" icon="plus" wire:click="create" data-test="new-product-button">
                {{ __('New product') }}
            </flux:button>
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Category') }}</flux:table.column>
            <flux:table.column>{{ __('Photos') }}</flux:table.column>
            <flux:table.column>{{ __('Price from') }}</flux:table.column>
            <flux:table.column>{{ __('Featured') }}</flux:table.column>
            <flux:table.column>{{ __('Visible') }}</flux:table.column>
            <flux:table.column />
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->products as $product)
                <flux:table.row wire:key="product-{{ $product->id }}">
                    <flux:table.cell variant="strong">{{ $product->name }}</flux:table.cell>
                    <flux:table.cell>{{ $product->category->name }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:badge size="sm" color="zinc">{{ $product->images_count }}</flux:badge>
                    </flux:table.cell>
                    <flux:table.cell>{{ $product->price_from ?? '—' }}</flux:table.cell>
                    <flux:table.cell>
                        @if ($product->is_featured)
                            <flux:badge size="sm" color="amber">{{ __('Featured') }}</flux:badge>
                        @endif
                    </flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$product->is_active" wire:click="toggleActive({{ $product->id }})" />
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $product->id }})" :aria-label="__('Edit')" />
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            wire:click="delete({{ $product->id }})"
                            wire:confirm="{{ __('Delete :name?', ['name' => $product->name]) }}"
                            :aria-label="__('Delete')"
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="7">{{ __('No products yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="product-form" class="w-full max-w-lg">
        <form
            wire:submit="save"
            class="space-y-5"
            x-data="{ uploadingPhotos: false }"
            x-on:livewire-upload-start="uploadingPhotos = true"
            x-on:livewire-upload-finish="uploadingPhotos = false"
            x-on:livewire-upload-error="uploadingPhotos = false"
            x-on:livewire-upload-cancel="uploadingPhotos = false"
        >
            <flux:heading size="lg">{{ $editingId ? __('Edit product') : __('New product') }}</flux:heading>

            <flux:select wire:model="category_id" :label="__('Category')" required>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" :label="__('Name')" required />
            <flux:input wire:model="slug" :label="__('Slug')" :description="__('Leave blank to generate from the name.')" />
            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />
            <flux:textarea wire:model="features" :label="__('Features')" :description="__('One per line.')" rows="4" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="price_from" :label="__('Price from')" placeholder="₱450" />
                <flux:input wire:model="minimum_order" :label="__('Minimum order')" placeholder="12 pcs" />
            </div>

            <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

            <div class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                <div>
                    <flux:heading size="sm">{{ __('Photos') }}</flux:heading>
                    <flux:text class="mt-1">
                        @if ($editingId)
                            {{ __('JPG, PNG, WebP, GIF or AVIF, up to 5 MB each. Photos are converted to WebP and saved as soon as they finish uploading.') }}
                        @else
                            {{ __('JPG, PNG, WebP, GIF or AVIF, up to 5 MB each. Photos are converted to WebP when you create the product.') }}
                        @endif
                    </flux:text>
                </div>

                @if ($this->existingPhotos->isNotEmpty())
                    <div class="grid grid-cols-3 gap-3" data-test="product-photos">
                        @foreach ($this->existingPhotos as $photo)
                            <div class="relative overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700" wire:key="photo-{{ $photo->id }}">
                                <img src="{{ $photo->url() }}" alt="" class="aspect-square w-full object-cover" />
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    class="absolute end-1 top-1"
                                    wire:click="deletePhoto({{ $photo->id }})"
                                    wire:confirm="{{ __('Delete this photo?') }}"
                                    :aria-label="__('Delete photo')"
                                />
                            </div>
                        @endforeach
                    </div>
                @endif

                <input
                    type="file"
                    wire:model="photoUploads"
                    multiple
                    accept="image/*"
                    data-test="product-photo-input"
                    class="block w-full text-sm text-zinc-500 file:me-3 file:rounded-lg file:border-0 file:bg-zinc-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-zinc-700 dark:text-zinc-400 dark:file:bg-zinc-700 dark:file:text-zinc-200"
                />

                <flux:text x-show="uploadingPhotos" x-cloak>
                    {{ __('Uploading…') }}
                </flux:text>

                @error('photoUploads.*')
                    <flux:text class="text-red-500">{{ $message }}</flux:text>
                @enderror

                @if (count($photoUploads) > 0)
                    <div class="grid grid-cols-3 gap-3">
                        @foreach ($photoUploads as $upload)
                            <div class="overflow-hidden rounded-lg border border-dashed border-zinc-300 dark:border-zinc-600" wire:key="upload-{{ $loop->index }}">
                                @if ($upload->isPreviewable())
                                    <img src="{{ $upload->temporaryUrl() }}" alt="" class="aspect-square w-full object-cover" />
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="flex gap-6">
                <flux:switch wire:model="is_featured" :label="__('Featured on home page')" />
                <flux:switch wire:model="is_active" :label="__('Visible on storefront')" />
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" data-test="save-product-button" x-bind:disabled="uploadingPhotos">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
