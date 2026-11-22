<?php

namespace App\Http\Controllers\Api;

use App\Display\DisplayPayload;
use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DisplayController extends Controller
{
    /**
     * Current display content. The ETag lets the display poll cheaply:
     * unchanged content answers 304 with no body.
     */
    public function __invoke(Request $request, DisplayPayload $payload): JsonResponse
    {
        $data = $payload->build();
        $response = response()->json($data);

        $response->setEtag(DisplayPayload::etag($data));
        $response->headers->set('Cache-Control', 'no-cache, private');
        $response->isNotModified($request);

        return $response;
    }
}
