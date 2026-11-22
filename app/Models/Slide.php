<?php

namespace App\Models;

use App\Models\Concerns\HasDisplayOrder;
use Database\Factories\SlideFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An entry in the display rotation. Its `type` selects the slide type
 * (see config/huifax.php) that turns it into one or more on-screen pages.
 */
#[Fillable(['type', 'name', 'sort_order', 'duration', 'is_enabled', 'settings'])]
class Slide extends Model
{
    /** @use HasFactory<SlideFactory> */
    use HasDisplayOrder, HasFactory;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'duration' => 'integer',
            'is_enabled' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Read a type-specific setting.
     */
    public function setting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }
}
