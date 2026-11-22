<?php

namespace App\Settings;

use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\Settings;

class BrandingSettings extends Settings
{
    public string $title;

    public ?string $tagline;

    /** Path on the public disk. */
    public ?string $logo_path;

    public static function group(): string
    {
        return 'branding';
    }

    public function logoUrl(): ?string
    {
        return $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null;
    }
}
