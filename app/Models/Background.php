<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use Database\Factories\BackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

/**
 * A background photo. Enabled backgrounds rotate behind every slide; any
 * background can also be assigned to a slide to replace the rotation.
 */
#[Fillable(['name', 'path', 'is_enabled', 'sort_order'])]
class Background extends Model
{
    /** @use HasFactory<BackgroundFactory> */
    use HasDisplayOrder, HasFactory;

    public const string DISK = 'public';

    public const string DIRECTORY = 'backgrounds';

    protected static function booted(): void
    {
        // Keep the disk in sync: replaced or deleted images leave no orphan files.
        static::updated(function (Background $background) {
            if ($background->wasChanged('path') && $background->getOriginal('path')) {
                Storage::disk(self::DISK)->delete($background->getOriginal('path'));
            }
        });

        static::deleted(function (Background $background) {
            Storage::disk(self::DISK)->delete($background->path);
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public function url(): string
    {
        return Storage::disk(self::DISK)->url($this->path);
    }
}
