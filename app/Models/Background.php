<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use App\Models\Concerns\HasStoredImage;
use Database\Factories\BackgroundFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A background photo. Enabled backgrounds rotate behind every slide; any
 * background can also be assigned to a slide to replace the rotation.
 */
#[Fillable(['name', 'path', 'is_enabled', 'sort_order'])]
class Background extends Model
{
    /** @use HasFactory<BackgroundFactory> */
    use HasDisplayOrder, HasFactory, HasStoredImage;

    public const string DIRECTORY = 'backgrounds';

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
}
