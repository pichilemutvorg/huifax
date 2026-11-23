<?php

namespace App\Media;

use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\Encoders\WebpEncoder;
use Intervention\Image\ImageManager;

/**
 * Re-encodes uploaded photos for the display: right-sized, WebP, without
 * metadata. A phone photo of several megabytes usually ends up at a few
 * hundred kilobytes with no visible loss at 1080p.
 */
class PhotoOptimizer
{
    /** File extension of the optimized output. */
    public const string EXTENSION = 'webp';

    /**
     * Memory for decoding large photos: GD keeps the whole bitmap in memory
     * (about 4 bytes per pixel), so a 24-megapixel photo needs ~100 MB.
     */
    private const string MEMORY_LIMIT = '512M';

    public function __construct(
        private int $width,
        private int $height,
        private int $quality,
    ) {}

    /**
     * Optimize the image at `$path` and return the encoded WebP bytes.
     *
     * The photo is scaled down (never up) until it just covers the target
     * size, keeping its aspect ratio; the display crops it with object-cover.
     * EXIF orientation is applied before metadata is stripped.
     */
    public function optimize(string $path): string
    {
        $this->ensureMemory();

        $image = $this->manager()->decodePath($path);

        $scale = max($this->width / $image->width(), $this->height / $image->height());

        if ($scale < 1) {
            $image->scale((int) round($image->width() * $scale), (int) round($image->height() * $scale));
        }

        return (string) $image->encode(new WebpEncoder(quality: $this->quality, strip: true));
    }

    private function manager(): ImageManager
    {
        $driver = extension_loaded('imagick') ? ImagickDriver::class : GdDriver::class;

        return new ImageManager($driver, autoOrientation: true, decodeAnimation: false, strip: true);
    }

    private function ensureMemory(): void
    {
        $current = ini_get('memory_limit');

        if ($current !== '-1' && $this->bytes($current) < $this->bytes(self::MEMORY_LIMIT)) {
            ini_set('memory_limit', self::MEMORY_LIMIT);
        }
    }

    private function bytes(string $value): int
    {
        $number = (int) $value;

        return match (strtoupper(substr(trim($value), -1))) {
            'G' => $number * 1024 ** 3,
            'M' => $number * 1024 ** 2,
            'K' => $number * 1024,
            default => $number,
        };
    }
}
