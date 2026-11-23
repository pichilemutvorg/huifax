<?php

namespace Tests\Feature\Media;

use App\Media\PhotoOptimizer;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class PhotoOptimizerTest extends TestCase
{
    /**
     * @return array{0: int, 1: int, mime: string}
     */
    private function optimizedSize(int $width, int $height): array
    {
        $photo = UploadedFile::fake()->image('photo.jpg', $width, $height);
        $optimized = app(PhotoOptimizer::class)->optimize($photo->getRealPath());

        return getimagesizefromstring($optimized);
    }

    public function test_large_photos_are_scaled_down_to_just_cover_the_stage(): void
    {
        // 4:3 photo: the width reaches 1920 first; the height stays above 1080 for cropping.
        $size = $this->optimizedSize(2400, 1800);

        $this->assertSame([1920, 1440, 'image/webp'], [$size[0], $size[1], $size['mime']]);
    }

    public function test_tall_photos_keep_the_height_needed_to_cover(): void
    {
        $size = $this->optimizedSize(2400, 3200);

        $this->assertSame([1920, 2560], [$size[0], $size[1]]);
    }

    public function test_small_photos_are_not_upscaled(): void
    {
        $size = $this->optimizedSize(1280, 720);

        $this->assertSame([1280, 720, 'image/webp'], [$size[0], $size[1], $size['mime']]);
    }
}
