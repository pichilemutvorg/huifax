<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Notice;
use App\Models\Slide;
use Carbon\CarbonImmutable;

/**
 * Shows each active notice as its own full-screen page.
 */
class NoticesSlide extends SlideType
{
    public function key(): string
    {
        return 'notices';
    }

    public function defaultDuration(): int
    {
        return 20;
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $heading = $slide->setting('heading') ?: __('display.slides.notices.heading');

        return Notice::query()
            ->enabled()
            ->activeAt($now)
            ->ordered()
            ->get(['id', 'title', 'body', 'duration'])
            ->map(fn (Notice $notice) => new SlidePage(
                key: "notice-{$notice->id}",
                data: [
                    'heading' => $heading,
                    'title' => $notice->title,
                    'body' => $notice->body,
                ],
                duration: $notice->duration,
            ))
            ->all();
    }
}
