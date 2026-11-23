<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use App\Models\Concerns\HasPublicationWindow;
use App\Models\Concerns\HasStoredImage;
use Database\Factories\FeaturedProgramFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A billboard promoting one of the channel's flagship programs. When it airs
 * is free text: the full schedule belongs to a dedicated planning system.
 */
#[Fillable(['name', 'tagline', 'schedule', 'label', 'path', 'starts_at', 'ends_at', 'duration', 'sort_order', 'is_enabled'])]
class FeaturedProgram extends Model
{
    /** @use HasFactory<FeaturedProgramFactory> */
    use HasDisplayOrder, HasFactory, HasPublicationWindow, HasStoredImage;

    public const string DIRECTORY = 'featured-programs';

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'duration' => 'integer',
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
        ];
    }
}
