<?php

namespace App\Http\Controllers;

use App\Display\DisplayPayload;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DisplayPageController extends Controller
{
    /**
     * The on-air display. The initial payload is embedded so the first frame
     * renders without waiting for the API.
     *
     * `?theme=<key>` forces a theme on this screen only, to try it before
     * changing the setting.
     */
    public function __invoke(Request $request, DisplayPayload $payload): View
    {
        $data = $payload->build();
        $theme = $request->query('theme');
        $themeOverride = is_string($theme) && in_array($theme, config('huifax.themes'), true) ? $theme : null;

        return view('display', [
            'payload' => $data,
            'etag' => '"'.DisplayPayload::etag($data).'"',
            'themeOverride' => $themeOverride,
        ]);
    }
}
