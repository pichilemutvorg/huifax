<?php

namespace App\Display;

/**
 * One on-screen page produced by a slide. A slide may produce several pages
 * (e.g. one per notice, or a long list split in chunks).
 */
final readonly class SlidePage
{
    /**
     * @param  string  $key  Identifier unique within its slide and stable across requests.
     * @param  array<string, mixed>  $data  Props for the display component.
     * @param  int|null  $duration  Seconds on screen; overrides the slide's duration.
     */
    public function __construct(
        public string $key,
        public array $data,
        public ?int $duration = null,
    ) {}
}
