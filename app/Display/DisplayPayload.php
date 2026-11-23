<?php

namespace App\Display;

use App\Models\Background;
use App\Models\Slide;
use App\Settings\AudioSettings;
use App\Settings\BrandingSettings;
use App\Settings\DisplaySettings;
use Carbon\CarbonImmutable;
use Throwable;

/**
 * Builds everything the display needs: instance settings, UI strings and the
 * current slide rotation.
 *
 * The payload must be deterministic for the same content: its hash is the
 * ETag the display polls against, so it must not include timestamps or other
 * values that change on every request.
 */
class DisplayPayload
{
    public function __construct(
        private SlideTypeRegistry $types,
        private BrandingSettings $branding,
        private DisplaySettings $display,
        private AudioSettings $audio,
    ) {}

    /**
     * @return array{settings: array<string, mixed>, strings: array<string, mixed>, backgrounds: list<array<string, mixed>>, slides: list<array<string, mixed>>}
     */
    public function build(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        return [
            'settings' => $this->settings(),
            'strings' => (array) __('display.ui'),
            'backgrounds' => $this->backgrounds(),
            'slides' => $this->slides($now),
        ];
    }

    /**
     * Version tag for a built payload, used as the HTTP ETag.
     *
     * @param  array<string, mixed>  $payload
     */
    public static function etag(array $payload): string
    {
        return hash('xxh128', json_encode($payload, JSON_THROW_ON_ERROR));
    }

    /**
     * @return array<string, mixed>
     */
    private function settings(): array
    {
        return [
            'locale' => config('huifax.display.locale'),
            'theme' => $this->display->resolvedTheme(),
            'timezone' => config('app.timezone'),
            'title' => $this->branding->title,
            'tagline' => $this->branding->tagline,
            'logo_url' => $this->branding->logoUrl(),
            'poll_interval' => $this->display->poll_interval,
            'reload_at' => $this->display->reload_at,
            'background_interval' => $this->display->background_interval,
            'audio' => [
                'enabled' => $this->audio->enabled,
                'stream_url' => $this->audio->stream_url,
                'volume' => $this->audio->volume,
            ],
        ];
    }

    /**
     * General background rotation.
     *
     * @return list<array<string, mixed>>
     */
    private function backgrounds(): array
    {
        return Background::query()
            ->enabled()
            ->ordered()
            ->get()
            ->map(fn (Background $background) => ['id' => $background->id, 'url' => $background->url()])
            ->all();
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function slides(CarbonImmutable $now): array
    {
        $items = [];

        foreach (Slide::query()->with('background')->enabled()->ordered()->get() as $slide) {
            $type = $this->types->find($slide->type);

            if ($type === null) {
                continue;
            }

            try {
                $pages = $type->pages($slide, $now);
            } catch (Throwable $e) {
                // A broken slide must never take the whole display down.
                report($e);

                continue;
            }

            foreach ($pages as $page) {
                $items[] = [
                    'id' => "{$slide->id}:{$page->key}",
                    'type' => $type->key(),
                    'duration' => $page->duration ?? $slide->duration ?? $type->defaultDuration(),
                    'background' => $slide->background?->url(),
                    'data' => $page->data,
                ];
            }
        }

        return $items;
    }
}
