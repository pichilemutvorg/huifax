<?php

namespace App\Http\Controllers;

use App\Display\DisplayPayload;
use App\Display\SlideTypeRegistry;
use App\Models\Slide;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

/**
 * Overview of the current rotation: every page the display will show, in
 * order and with its timing, rendered by the display's own components.
 * Shown inside the admin panel's preview page (in an iframe, so the display
 * styles never mix with the panel's).
 */
class PreviewFrameController extends Controller
{
    public function __invoke(Request $request, DisplayPayload $payload, SlideTypeRegistry $types): View
    {
        $data = $payload->build();

        // The preview must never play the radio, whatever the display does.
        $data['settings']['audio']['enabled'] = false;

        $theme = $request->query('theme');
        if (is_string($theme) && in_array($theme, config('huifax.themes'), true)) {
            $data['settings']['theme'] = $theme;
        }

        // Names to tell slides apart in the overview, keyed by slide id.
        $names = Slide::query()
            ->get(['id', 'name', 'type'])
            ->mapWithKeys(fn (Slide $slide) => [
                $slide->id => $slide->name ?: ($types->find($slide->type)?->label() ?? $slide->type),
            ]);

        return view('preview', [
            'payload' => $data,
            'names' => $names,
            'strings' => [
                'slides' => __('admin.preview.slides'),
                'cycle' => __('admin.preview.cycle'),
                'page' => __('admin.preview.page'),
                'empty' => __('admin.preview.empty'),
                'seconds' => __('admin.preview.seconds'),
            ],
        ]);
    }
}
