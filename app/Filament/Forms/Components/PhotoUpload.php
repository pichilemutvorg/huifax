<?php

namespace App\Filament\Forms\Components;

use App\Media\PhotoOptimizer;
use Filament\Forms\Components\FileUpload;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Image upload for photos shown on the display: accepts common formats up to
 * the configured size and stores an optimized WebP copy instead of the
 * original (see PhotoOptimizer).
 */
class PhotoUpload extends FileUpload
{
    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->image()
            ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
            ->maxSize(config('huifax.uploads.max_size'))
            ->saveUploadedFileUsing(function (PhotoUpload $component, TemporaryUploadedFile $file): string {
                $path = trim($component->getDirectory().'/'.Str::random(40).'.'.PhotoOptimizer::EXTENSION, '/');

                $component->getDisk()->put($path, app(PhotoOptimizer::class)->optimize($file->getRealPath()));

                return $path;
            });
    }
}
