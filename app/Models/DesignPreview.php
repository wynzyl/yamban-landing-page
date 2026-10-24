<?php

namespace App\Models;

use Database\Factories\DesignPreviewFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $design_id
 * @property string $data
 * @property string $mime
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['design_id', 'data', 'mime'])]
class DesignPreview extends Model
{
    /** @use HasFactory<DesignPreviewFactory> */
    use HasFactory;

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = ['data'];

    /**
     * The design this preview belongs to.
     *
     * @return BelongsTo<Design, $this>
     */
    public function design(): BelongsTo
    {
        return $this->belongsTo(Design::class);
    }

    /**
     * Decode the stored base64 preview into raw image bytes.
     */
    public function binary(): ?string
    {
        $binary = base64_decode($this->data, true);

        return $binary === false ? null : $binary;
    }
}
