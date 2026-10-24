<?php

namespace App\Models;

use Database\Factories\DesignFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property int $category_id
 * @property string $name
 * @property string $drive_url
 * @property string|null $thumbnail_url
 * @property bool $has_preview
 * @property bool $is_active
 * @property int $sort_order
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['category_id', 'name', 'drive_url', 'thumbnail_url', 'has_preview', 'is_active', 'sort_order'])]
class Design extends Model
{
    /** @use HasFactory<DesignFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'has_preview' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * The category the design belongs to.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The stored preview image for the design.
     *
     * @return HasOne<DesignPreview, $this>
     */
    public function preview(): HasOne
    {
        return $this->hasOne(DesignPreview::class);
    }

    /**
     * Limit the query to visible designs.
     *
     * @param  Builder<Design>  $query
     */
    public function scopeActive(Builder $query): void
    {
        $query->where('is_active', true);
    }

    /**
     * Limit the query to the design library display order.
     *
     * @param  Builder<Design>  $query
     */
    public function scopeOrdered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * The Google Drive link visitors open to view the design.
     */
    public function viewUrl(): string
    {
        return $this->drive_url;
    }

    /**
     * Resolve a preview image for the design, when one can be derived.
     */
    public function previewUrl(): ?string
    {
        if ($this->has_preview) {
            return route('designs.preview', [
                'design' => $this,
                'v' => $this->updated_at?->timestamp ?? $this->id,
            ]);
        }

        if (filled($this->thumbnail_url)) {
            return Str::startsWith($this->thumbnail_url, ['http://', 'https://', '/'])
                ? $this->thumbnail_url
                : Storage::disk('public')->url($this->thumbnail_url);
        }

        $fileId = $this->driveFileId();

        return $fileId
            ? "https://drive.google.com/thumbnail?id={$fileId}&sz=w1000"
            : null;
    }

    /**
     * Extract the Google Drive file ID from the shared link.
     */
    public function driveFileId(): ?string
    {
        if (preg_match('#/d/([a-zA-Z0-9_-]+)#', $this->drive_url, $matches)) {
            return $matches[1];
        }

        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $this->drive_url, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
