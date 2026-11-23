<?php

namespace App\Models\Concerns;

use Illuminate\Support\Facades\Storage;

/**
 * A model that owns one uploaded image on the public disk, in the `path`
 * column. Replaced or deleted images leave no orphan files.
 */
trait HasStoredImage
{
    public const string DISK = 'public';

    protected static function bootHasStoredImage(): void
    {
        static::updated(function (self $model) {
            if ($model->wasChanged('path') && $model->getOriginal('path')) {
                Storage::disk(self::DISK)->delete($model->getOriginal('path'));
            }
        });

        static::deleted(function (self $model) {
            if ($model->path) {
                Storage::disk(self::DISK)->delete($model->path);
            }
        });
    }

    public function url(): string
    {
        return Storage::disk(self::DISK)->url($this->path);
    }
}
