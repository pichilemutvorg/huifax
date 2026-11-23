@php
    $title = $payload['settings']['title'];
    $tagline = $payload['settings']['tagline'];
@endphp

<!doctype html>
<html lang="{{ $payload['settings']['locale'] }}" data-theme="{{ $themeOverride ?? $payload['settings']['theme'] }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $tagline ? __('display.document_title', ['title' => $title, 'tagline' => $tagline]) : $title }}</title>
    @vite (['resources/css/display.css', 'resources/js/display/main.js'])
</head>
<body>
    <div
        id="display"
        data-api-url="{{ route('api.display') }}"
        data-health-url="{{ url('/up') }}"
        data-service-worker-url="{{ asset('sw.js') }}"
        data-etag="{{ $etag }}"
        data-theme-override="{{ $themeOverride }}"
    ></div>
    <script type="application/json" id="display-payload">
        {!! json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) !!}
    </script>
</body>
</html>
