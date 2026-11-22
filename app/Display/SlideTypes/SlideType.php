<?php

namespace App\Display\SlideTypes;

use App\Display\SlidePage;
use App\Models\Slide;
use Carbon\CarbonImmutable;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Component;

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
     * Name shown in the admin panel.
     */
    public function label(): string
    {
        return __("admin.slide_types.{$this->key()}");
    }

    /**
     * Admin panel fields for this type's `settings`. Field names must be
     * prefixed with `settings.`.
     *
     * @return list<Component>
     */
    public function settingsFields(): array
    {
        return [
            TextInput::make('settings.heading')
                ->label(__('admin.slides.fields.heading'))
                ->placeholder(__("display.slides.{$this->key()}.heading"))
                ->helperText(__('admin.slides.help.heading'))
                ->maxLength(80),
        ];
    }

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
