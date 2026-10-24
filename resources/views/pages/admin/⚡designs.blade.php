<?php

use App\Jobs\GenerateDesignThumbnails;
use App\Models\Category;
use App\Models\Design;
use App\Support\GoogleDriveDesignImport;
use Flux\Flux;
use Illuminate\Support\Str;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Title;
use Livewire\Component;

new #[Title('Manage designs')] class extends Component {
    public ?int $editingId = null;

    public string $filterCategory = 'all';

    public string $search = '';

    public ?int $category_id = null;
    public string $name = '';
    public string $drive_url = '';
    public string $thumbnail_url = '';
    public int $sort_order = 0;
    public bool $is_active = true;

    public ?int $bulk_category_id = null;
    public string $bulk_links = '';

    /**
     * Categories available for assignment and filtering.
     */
    #[Computed]
    public function categories()
    {
        return Category::query()->ordered()->get();
    }

    /**
     * Designs matching the category and search filters.
     */
    #[Computed]
    public function designs()
    {
        return Design::query()
            ->with('category')
            ->when($this->filterCategory !== 'all', fn ($query) => $query->where('category_id', $this->filterCategory))
            ->when(
                filled($this->search),
                fn ($query) => $query->where(function ($query): void {
                    $query->whereLike('name', '%'.$this->search.'%')
                        ->orWhereHas('category', fn ($category) => $category->whereLike('name', '%'.$this->search.'%'));
                }),
            )
            ->orderBy('category_id')
            ->ordered()
            ->get();
    }

    /**
     * Whether a Google Drive API key is available for folder imports.
     */
    #[Computed]
    public function driveConfigured(): bool
    {
        return filled(config('services.google.drive_api_key'));
    }

    /**
     * Designs that do not have a stored preview yet.
     */
    #[Computed]
    public function missingPreviews(): int
    {
        return Design::query()
            ->where('has_preview', false)
            ->count();
    }

    /**
     * Open the form for a new design.
     */
    public function create(): void
    {
        $this->resetForm();
        $this->category_id = $this->filterCategory !== 'all' ? (int) $this->filterCategory : $this->categories->first()?->id;

        Flux::modal('design-form')->show();
    }

    /**
     * Open the form for pasting a batch of Google Drive links.
     */
    public function openBulkImport(): void
    {
        $this->resetValidation();
        $this->reset('bulk_links');
        $this->bulk_category_id = $this->filterCategory !== 'all' ? (int) $this->filterCategory : $this->categories->first()?->id;

        Flux::modal('design-bulk-form')->show();
    }

    /**
     * Save every pasted Google Drive link as a design in the selected category.
     */
    public function bulkImport(): void
    {
        $validated = $this->validate([
            'bulk_category_id' => ['required', 'integer', 'exists:categories,id'],
            'bulk_links' => ['required', 'string', 'max:50000'],
        ]);

        try {
            $result = app(GoogleDriveDesignImport::class)->import(
                $validated['bulk_links'],
                Category::query()->findOrFail($validated['bulk_category_id']),
            );
        } catch (\Throwable $exception) {
            $this->addError('bulk_links', $exception->getMessage());
            Flux::toast(variant: 'danger', text: $exception->getMessage());

            return;
        }

        if ($result['imported'] === 0 && $result['skipped'] === 0) {
            $message = match (true) {
                $result['non_images'] > 0 => __('No image files were found. Only JPG, PNG, WebP, GIF and AVIF files are imported.'),
                $result['folders'] > 0 && $result['files'] === 0 => __('No files were found in that Google Drive folder. Subfolders are included, so check that it contains files and is shared with “Anyone with the link”.'),
                default => __('No Google Drive folder or file links were found in what you pasted.'),
            };

            $this->addError('bulk_links', $message);
            Flux::toast(variant: 'danger', text: $message);

            return;
        }

        $message = __(':imported imported', ['imported' => $result['imported']]);

        if ($result['skipped'] > 0) {
            $message .= ', '.__(':count skipped', ['count' => $result['skipped']]);
        }

        if ($result['non_images'] > 0) {
            $message .= ', '.__(':count non-image files ignored', ['count' => $result['non_images']]);
        }

        if ($result['invalid'] > 0) {
            $message .= ', '.__(':count invalid', ['count' => $result['invalid']]);
        }

        $message .= '.';

        if ($result['imported'] > 0) {
            $message .= ' '.__('Previews are being generated in the background.');
            GenerateDesignThumbnails::dispatch();
        }

        Flux::modal('design-bulk-form')->close();
        Flux::toast(variant: 'success', text: $message);

        unset($this->designs, $this->missingPreviews);
        $this->reset('bulk_links');
    }

    /**
     * Queue preview generation for designs that do not have one yet.
     */
    public function generatePreviews(): void
    {
        if ($this->missingPreviews === 0) {
            Flux::toast(text: __('Every design already has a preview.'));

            return;
        }

        GenerateDesignThumbnails::dispatch();

        Flux::toast(variant: 'success', text: __('Previews are being generated in the background. Refresh in a moment.'));
    }

    /**
     * Open the form pre-filled with an existing design.
     */
    public function edit(int $id): void
    {
        $design = Design::query()->findOrFail($id);

        $this->resetValidation();
        $this->editingId = $design->id;
        $this->category_id = $design->category_id;
        $this->name = $design->name;
        $this->drive_url = $design->drive_url;
        $this->thumbnail_url = (string) $design->thumbnail_url;
        $this->sort_order = $design->sort_order;
        $this->is_active = $design->is_active;

        Flux::modal('design-form')->show();
    }

    /**
     * Create or update the design being edited.
     */
    public function save(): void
    {
        $this->drive_url = trim($this->drive_url);

        if (filled($this->drive_url) && ! Str::startsWith($this->drive_url, ['http://', 'https://'])) {
            $this->drive_url = 'https://'.$this->drive_url;
        }

        $this->thumbnail_url = trim($this->thumbnail_url);

        $validated = $this->validate([
            'category_id' => ['required', 'integer', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'drive_url' => ['required', 'url', 'max:2048', 'starts_with:https://drive.google.com,https://docs.google.com'],
            'thumbnail_url' => ['nullable', 'url', 'max:2048'],
            'sort_order' => ['required', 'integer', 'min:0'],
            'is_active' => ['boolean'],
        ]);

        $validated['thumbnail_url'] = filled($validated['thumbnail_url']) ? $validated['thumbnail_url'] : null;

        Design::query()->updateOrCreate(['id' => $this->editingId], $validated);

        Flux::modal('design-form')->close();
        Flux::toast(variant: 'success', text: $this->editingId ? __('Design updated.') : __('Design created.'));

        $this->resetForm();
        unset($this->designs, $this->missingPreviews);
    }

    /**
     * Show or hide the design on the design library.
     */
    public function toggleActive(int $id): void
    {
        $design = Design::query()->findOrFail($id);
        $design->update(['is_active' => ! $design->is_active]);

        unset($this->designs);
    }

    /**
     * Delete a design.
     */
    public function delete(int $id): void
    {
        Design::query()->findOrFail($id)->delete();

        Flux::toast(variant: 'success', text: __('Design deleted.'));

        unset($this->designs, $this->missingPreviews);
    }

    private function resetForm(): void
    {
        $this->resetValidation();
        $this->reset('editingId', 'category_id', 'name', 'drive_url', 'thumbnail_url', 'sort_order', 'is_active');
    }
};
?>

<section class="w-full">
    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <flux:heading size="xl" level="1">{{ __('Designs') }}</flux:heading>
            <flux:subheading>{{ __('Manage the design library linked from Google Drive.') }}</flux:subheading>
        </div>

        <div class="flex w-full flex-wrap items-center gap-3 sm:w-auto">
            <flux:input
                wire:model.live.debounce.300ms="search"
                icon="magnifying-glass"
                clearable
                :placeholder="__('Search designs or categories…')"
                :aria-label="__('Search designs or categories')"
                class="w-full sm:w-64"
            />

            <flux:select wire:model.live="filterCategory" class="min-w-48" :aria-label="__('Filter by category')">
                <flux:select.option value="all">{{ __('All categories') }}</flux:select.option>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:button variant="filled" icon="folder-open" wire:click="openBulkImport" data-test="bulk-design-button">
                {{ __('Bulk add') }}
            </flux:button>

            @if ($this->missingPreviews > 0)
                <flux:button variant="ghost" icon="photo" wire:click="generatePreviews" data-test="generate-previews-button">
                    {{ __('Generate previews') }}
                </flux:button>
            @endif

            <flux:button variant="primary" icon="plus" wire:click="create" data-test="new-design-button">
                {{ __('New design') }}
            </flux:button>
        </div>
    </div>

    <flux:table>
        <flux:table.columns>
            <flux:table.column>{{ __('Design') }}</flux:table.column>
            <flux:table.column>{{ __('Category') }}</flux:table.column>
            <flux:table.column>{{ __('Order') }}</flux:table.column>
            <flux:table.column>{{ __('Visible') }}</flux:table.column>
            <flux:table.column />
        </flux:table.columns>

        <flux:table.rows>
            @forelse ($this->designs as $design)
                <flux:table.row wire:key="design-{{ $design->id }}">
                    <flux:table.cell variant="strong">
                        <div class="flex items-center gap-3">
                            @if ($design->previewUrl())
                                <img src="{{ $design->previewUrl() }}" alt="" class="size-10 shrink-0 rounded-md border border-zinc-200 object-cover dark:border-zinc-700" onerror="this.style.display = 'none'" />
                            @else
                                <span class="grid size-10 shrink-0 place-items-center rounded-md border border-dashed border-zinc-300 text-zinc-400 dark:border-zinc-600">
                                    <flux:icon.photo class="size-4" />
                                </span>
                            @endif

                            <span class="min-w-0">
                                <span class="block truncate">{{ $design->name }}</span>
                                <a href="{{ $design->drive_url }}" target="_blank" rel="noopener noreferrer" class="block text-xs font-normal text-zinc-500 underline-offset-4 hover:underline">
                                    {{ __('Open in Google Drive') }}
                                </a>
                            </span>
                        </div>
                    </flux:table.cell>
                    <flux:table.cell>{{ $design->category->name }}</flux:table.cell>
                    <flux:table.cell>{{ $design->sort_order }}</flux:table.cell>
                    <flux:table.cell>
                        <flux:switch :checked="$design->is_active" wire:click="toggleActive({{ $design->id }})" />
                    </flux:table.cell>
                    <flux:table.cell class="text-end">
                        <flux:button size="sm" variant="ghost" icon="pencil-square" wire:click="edit({{ $design->id }})" :aria-label="__('Edit')" />
                        <flux:button
                            size="sm"
                            variant="ghost"
                            icon="trash"
                            wire:click="delete({{ $design->id }})"
                            wire:confirm="{{ __('Delete :name?', ['name' => $design->name]) }}"
                            :aria-label="__('Delete')"
                        />
                    </flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="5">
                        {{ filled($search) || $filterCategory !== 'all' ? __('No designs match your filters.') : __('No designs yet.') }}
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
        </flux:table.rows>
    </flux:table>

    <flux:modal name="design-form" class="w-full max-w-lg">
        <form wire:submit="save" class="space-y-5">
            <flux:heading size="lg">{{ $editingId ? __('Edit design') : __('New design') }}</flux:heading>

            <flux:select wire:model="category_id" :label="__('Category')" required>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" :label="__('Design filename')" placeholder="Basketball Jersey - Home v1.png" required />
            <flux:input
                wire:model="drive_url"
                :label="__('Google Drive link')"
                type="url"
                placeholder="https://drive.google.com/file/d/…/view"
                :description="__('Paste the shared Google Drive or Google Docs link.')"
                required
            />
            <flux:input
                wire:model="thumbnail_url"
                :label="__('Thumbnail URL')"
                type="url"
                :description="__('Optional. Used only when no preview has been stored for the design yet.')"
            />

            <flux:input wire:model="sort_order" :label="__('Sort order')" type="number" min="0" />

            <flux:switch wire:model="is_active" :label="__('Visible in the design library')" />

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" data-test="save-design-button">{{ __('Save') }}</flux:button>
            </div>
        </form>
    </flux:modal>

    <flux:modal name="design-bulk-form" class="w-full max-w-lg">
        <form wire:submit="bulkImport" class="space-y-5">
            <div>
                <flux:heading size="lg">{{ __('Bulk add designs') }}</flux:heading>
                <flux:text class="mt-1">{{ __('Paste a Google Drive folder link, or one file link per line. Folder contents, including subfolders, are imported. Only JPG, PNG, WebP, GIF and AVIF images are added. The Drive filename becomes the design name; use “Design name | link” to set it yourself.') }}</flux:text>
            </div>

            @unless ($this->driveConfigured)
                <div class="rounded-lg border border-amber-300 bg-amber-50 p-4 text-sm text-amber-900 dark:border-amber-700 dark:bg-amber-950/40 dark:text-amber-200">
                    {{ __('Add GOOGLE_DRIVE_API_KEY to your .env file to import a folder and read filenames from Drive. Without it, file links are still saved, named after the file ID.') }}
                </div>
            @endunless

            <flux:select wire:model="bulk_category_id" :label="__('Category')" required>
                @foreach ($this->categories as $category)
                    <flux:select.option :value="$category->id">{{ $category->name }}</flux:select.option>
                @endforeach
            </flux:select>

            <flux:textarea
                wire:model="bulk_links"
                :label="__('Google Drive folder or file links')"
                rows="6"
                placeholder="https://drive.google.com/drive/folders/1AbC…&#10;https://drive.google.com/file/d/1XyZ…/view&#10;Basketball Jersey - Home.png | https://drive.google.com/file/d/1XyZ…/view"
                :description="__('Share the folder or files with “Anyone with the link”. Designs already in the selected category are skipped.')"
                required
            />

            @error('bulk_links')
                <flux:text class="text-red-500">{{ $message }}</flux:text>
            @enderror

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="ghost">{{ __('Cancel') }}</flux:button>
                </flux:modal.close>
                <flux:button
                    type="submit"
                    variant="primary"
                    data-test="import-designs-button"
                    wire:loading.attr="disabled"
                    wire:target="bulkImport"
                >
                    {{ __('Import designs') }}
                </flux:button>
            </div>
        </form>
    </flux:modal>
</section>
