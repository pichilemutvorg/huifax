<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\FeaturedProgram;
use App\Models\Slide;
use Carbon\CarbonImmutable;

/**
 * Shows each active featured program as its own full-screen billboard.
 */
class FeaturedProgramsSlide extends SlideType
{
    public function key(): string
    {
        return 'featured_programs';
    }

    public function defaultDuration(): int
    {
        return 15;
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $heading = $slide->setting('heading') ?: __('display.slides.featured_programs.heading');

        return FeaturedProgram::query()
            ->enabled()
            ->activeAt($now)
            ->ordered()
            ->get()
            ->map(fn (FeaturedProgram $program) => new SlidePage(
                key: "program-{$program->id}",
                data: [
                    'heading' => $heading,
                    'name' => $program->name,
                    'tagline' => $program->tagline,
                    'schedule' => $program->schedule,
                    'label' => $program->label,
                    'image' => $program->url(),
                ],
                duration: $program->duration,
            ))
            ->all();
    }
}
