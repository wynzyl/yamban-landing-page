<?php

use App\Models\Category;
use Flux\Flux;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage categories')] class extends Component {
    public ?int $editingId = null;

    public string $name = '';
    public string $slug = '';
    public string $tagline = '';
    public string $description = '';
    public string $accent_color = '#ff5a1f';
    public int $sort_order = 0;
    public bool $is_active = true;

    /**
     * Every category, in storefront order, with its product count.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()->ordered()->withCount('products')->get();
    }

    /**
     * Open the form for a new category.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->sort_order = (int) Category::query()->max('sort_order') + 1;

        Flux::modal('category-form')->show();
    }

    /**
     * Open the form pre-filled with an existing category.
     */
    public function edit(int $id): void
    {
        $category = Category::query()->findOrFail($id);

        $this->resetValidation();
        $this->editingId = $category->id;
        $this->name = $category->name;
        $this->slug = $category->slug;
        $this->tagline = (string) $category->tagline;
        $this->description = (string) $category->description;
        $this->accent_color = $category->accent_color ?? '#ff5a1f';
        $this->sort_order = $category->sort_order;
        $this->is_active = $category->is_active;

        Flux::modal('category-form')->show();
    }

    /**
     * Create or update the category being edited.
     */
    public function save(): void
    {
        $this->slug = Str::slug(filled($this->slug) ? $this->slug : $this->name);

        $validated = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'tagline' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'accent_color' => ['nullable', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        Category::query()->updateOrCreate(['id' => $this->editingId], $validated);

        Flux::modal('category-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? __('Category updated.') : __('Category created.'));

        $this->resetForm();
        unset($this->categories);
    }

    /**
     * Show or hide the category on the storefront.
     */
    public function toggleActive(int $id): void
    {
        $category = Category::query()->findOrFail($id);
        $category->update(['is_active' => ! $category->is_active]);

        unset($this->categories);
    }

    /**
     * Delete a category together with its products.
     */
    public function delete(int $id): void
    {
        Category::query()->findOrFail($id)->delete();

        Flux::toast(variant: 'success', text: __('Category deleted.'));

        unset($this->categories);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'name', 'slug', 'tagline', 'description', 'sort_order', 'is_active');
        $this->accent_color = '#ff5a1f';
    }
};
?>

<section class="w-full">
    <div class="mb-6 flex items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Categories') }}</flux:heading>
            <flux:subheading>{{ __('Manage the categories shown on the storefront.') }}</flux:subheading>
        </div>

        <flux:button variant="primary" icon="plus" wire:click="create" data-test="new-category-button">
            {{ __('New category') }}
        </flux:button>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Name') }}</flux:table.column>
            <flux:table.column>{{ __('Products') }}</flux:table.column>
            <flux:table.column>{{ __('Order') }}</flux:table.column>
            <flux:table.column>{{ __('Visible') }}</flux:table.column>
            <flux:table.column />
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->categories as $category)
                <flux:table.row wire:key="category-{{ $category->id }}">
                    <flux:table.cell variant="strong">
                        <span class="mr-2 inline-block size-3 rounded-full align-middle" style="background-color: {{ $category->accent_color }}"></span>
                        {{ $category->name }}
                    </flux:table.cell>
                    <flux:table.cell>{{ $category->products_count }}</flux:table.cell>
                    <flux:table.cell>{{ $category->sort_order }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$category->is_active" wire:click="toggleActive({{ $category->id }})" />
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $category->id }})" :aria-label="__('Edit')" />
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            wire:click="delete({{ $category->id }})"
                            wire:confirm="{{ __('Delete :name and its :count products?', ['name' => $category->name, 'count' => $category->products_count]) }}"
                            :aria-label="__('Delete')"
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">{{ __('No categories yet.') }}</flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="category-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">{{ $editingId ? __('Edit category') : __('New category') }}</flux:heading>

            <flux:input wire:model="name" :label="__('Name')" required autofocus />
            <flux:input wire:model="slug" :label="__('Slug')" :description="__('Leave blank to generate from the name.')" />
            <flux:input wire:model="tagline" :label="__('Tagline')" />
            <flux:textarea wire:model="description" :label="__('Description')" rows="3" />

            <div class="grid grid-cols-2 gap-4">
                <flux:input wire:model="accent_color" :label="__('Accent color')" type="color" />
                <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />
            </div>

            <flux:switch wire:model="is_active" :label="__('Visible on storefront')" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" data-test="save-category-button">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>
</section>
