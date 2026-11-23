<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Slide;
use App\Schedule\Airing;
use App\Schedule\ScheduleResolver;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;

/**
 * The rest of today's schedule or, once today has nothing left, tomorrow's.
 */
class DailyScheduleSlide extends SlideType
{
    public const int DEFAULT_PER_PAGE = 6;

    public function __construct(private ScheduleResolver $schedule) {}

    public function key(): string
    {
        return 'daily_schedule';
    }

    public function defaultDuration(): int
    {
        return 20;
    }

    public function settingsFields(): array
    {
        return [
            ...parent::settingsFields(),
            TextInput::make('settings.per_page')
                ->label(__('admin.slides.fields.per_page'))
                ->helperText(__('admin.slides.help.per_page'))
                ->integer()
                ->minValue(1)
                ->maxValue(6)
                ->placeholder((string) self::DEFAULT_PER_PAGE),
        ];
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $now = $now->setTimezone(config('app.timezone'));
        $date = $now->startOfDay();
        $airings = $this->schedule->between($now, $date->addDay());

        if ($airings === []) {
            $date = $date->addDay();
            $airings = $this->schedule->between($date, $date->addDay());
        }

        $perPage = max(1, (int) $slide->setting('per_page', self::DEFAULT_PER_PAGE));
        $chunks = array_chunk($airings, $perPage);
        $heading = $slide->setting('heading') ?: __('display.slides.daily_schedule.heading');

        return array_map(fn (array $chunk, int $index) => new SlidePage(
            key: "{$date->toDateString()}-{$index}",
            data: [
                'heading' => $heading,
                'date' => $date->toDateString(),
                'page' => $index + 1,
                'pages' => count($chunks),
                'airings' => array_map(fn (Airing $airing) => $airing->toDisplay(), $chunk),
            ],
        ), $chunks, array_keys($chunks));
    }
}
