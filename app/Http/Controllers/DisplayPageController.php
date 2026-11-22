<?php

namespace App\Http\Controllers;

use App\Display\DisplayPayload;
use Illuminate\Contracts\View\View;

class DisplayPageController extends Controller
{
    /**
     * The on-air display. The initial payload is embedded so the first frame
     * renders without waiting for the API.
     */
    public function __invoke(DisplayPayload $payload): View
    {
        $data = $payload->build();

        return view('display', [
            'payload' => $data,
            'etag' => '"'.DisplayPayload::etag($data).'"',
        ]);
    }
}
