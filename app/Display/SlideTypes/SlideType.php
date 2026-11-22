<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Slide;
use Carbon\CarbonImmutable;

/**
 * A kind of slide. Each type pairs this class (data) with a Vue component of
 * the same key in resources/js/display/slides (presentation).
 *
 * To add a type: extend this class, register it in config/huifax.php and add
 * its component to the display's slide registry.
 */
abstract class SlideType
{
    /**
     * Identifier stored in `slides.type` and used to pick the Vue component.
     */
    abstract public function key(): string;

    /**
     * Seconds each page stays on screen when neither the page nor the slide sets it.
     */
    abstract public function defaultDuration(): int;

    /**
     * Build the pages to show. Returning none means the slide is unavailable
     * right now and is skipped.
     *
     * @return list<SlidePage>
     */
    abstract public function pages(Slide $slide, CarbonImmutable $now): array;
}
