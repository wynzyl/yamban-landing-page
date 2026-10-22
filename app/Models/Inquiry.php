<?php

namespace App\Models;

use Database\Factories\InquiryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string|null $phone
 * @property string|null $company
 * @property int|null $category_id
 * @property int|null $product_id
 * @property int|null $quantity
 * @property string $message
 * @property string $status
 * @property string|null $ip_address
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'email', 'phone', 'company', 'category_id', 'product_id', 'quantity', 'message', 'status', 'ip_address'])]
class Inquiry extends Model
{
    /** @use HasFactory<InquiryFactory> */
    use HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
        ];
    }

    /**
     * The category the customer is interested in.
     *
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * The product the customer is interested in.
     *
     * @return BelongsTo<Product, $this>
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Limit the query to inquiries that still need a reply.
     *
     * @param  Builder<Inquiry>  $query
     */
    public function scopeNew(Builder $query): void
    {
        $query->where('status', 'new');
    }

    /**
     * A short label describing what the customer asked about.
     */
    public function interestLabel(): string
    {
        return $this->product?->name
            ?? $this->category?->name
            ?? 'General inquiry';
    }
}
