<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\EmergencyContact;
use App\Models\Slide;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;

class EmergencyContactsSlide extends SlideType
{
    public const int DEFAULT_PER_PAGE = 6;

    public function key(): string
    {
        return 'emergency_contacts';
    }

    public function defaultDuration(): int
    {
        return 15;
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
                ->maxValue(12)
                ->placeholder((string) self::DEFAULT_PER_PAGE),
        ];
    }

    public function pages(Slide $slide, CarbonImmutable $now): array
    {
        $perPage = max(1, (int) $slide->setting('per_page', self::DEFAULT_PER_PAGE));
        $heading = $slide->setting('heading') ?: __('display.slides.emergency_contacts.heading');

        $chunks = EmergencyContact::query()
            ->enabled()
            ->ordered()
            ->get(['id', 'name', 'phone'])
            ->chunk($perPage)
            ->values();

        return $chunks
            ->map(fn ($chunk, int $index) => new SlidePage(
                key: "page-{$index}",
                data: [
                    'heading' => $heading,
                    'page' => $index + 1,
                    'pages' => $chunks->count(),
                    'contacts' => $chunk->map(fn (EmergencyContact $contact) => [
                        'id' => $contact->id,
                        'name' => $contact->name,
                        'phone' => $contact->phone,
                    ])->values()->all(),
                ],
            ))
            ->all();
    }
}
