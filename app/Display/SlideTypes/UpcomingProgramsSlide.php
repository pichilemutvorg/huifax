<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Slide;
use App\Schedule\Airing;
use App\Schedule\ScheduleResolver;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;

/**
 * The next few airings from now on, whatever day they fall on.
 */
class UpcomingProgramsSlide extends SlideType
{
    public const int DEFAULT_COUNT = 6;

    /**
     * Extra airings sent beyond `count`, so the display can drop the ones
     * that start while it waits for fresh content and still fill the list.
     */
    public const int SPARE = 3;

    public function __construct(private ScheduleResolver $schedule) {}

    public function key(): string
    {
        return 'upcoming_programs';
    }

    public function defaultDuration(): int
    {
        return 20;
    }

    public function settingsFields(): array
    {
        return [
            ...parent::settingsFields(),
            TextInput::make('settings.count')
                ->label(__('admin.slides.fields.count'))
                ->integer()
                ->minValue(1)
                ->maxValue(6)
                ->placeholder((string) self::DEFAULT_COUNT),
        ];
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $count = max(1, (int) $slide->setting('count', self::DEFAULT_COUNT));
        $airings = $this->schedule->upcoming($now, $count + self::SPARE);

        if ($airings === []) {
            return [];
        }

        return [new SlidePage(
            key: 'upcoming',
            data: [
                'heading' => $slide->setting('heading') ?: __('display.slides.upcoming_programs.heading'),
                'count' => $count,
                'airings' => array_map(fn (Airing $airing) => $airing->toDisplay(), $airings),
            ],
        )];
    }
}
