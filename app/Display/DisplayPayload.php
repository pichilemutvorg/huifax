<?php

namespace App\Display;

use App\Models\Slide;
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
    public function __construct(private SlideTypeRegistry $types) {}

    /**
     * @return array{settings: array<string, mixed>, strings: array<string, mixed>, slides: list<array<string, mixed>>}
     */
    public function build(?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now();

        return [
            'settings' => $this->settings(),
            'strings' => (array) __('display.ui'),
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
            'timezone' => config('app.timezone'),
            'title' => config('huifax.branding.title'),
            'tagline' => config('huifax.branding.tagline'),
            'logo_url' => config('huifax.branding.logo_url'),
            'poll_interval' => (int) config('huifax.display.poll_interval'),
            'reload_at' => config('huifax.display.reload_at'),
        ];
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function slides(CarbonImmutable $now): array
    {
        $items = [];

        foreach (Slide::query()->enabled()->ordered()->get() as $slide) {
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
                    'data' => $page->data,
                ];
            }
        }

        return $items;
    }
}
