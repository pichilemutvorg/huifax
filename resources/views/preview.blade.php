<!doctype html>
<html lang="{{ $payload['settings']['locale'] }}" data-theme="{{ $payload['settings']['theme'] }}">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ __('admin.preview.title') }}</title>
    @vite (['resources/css/display.css', 'resources/js/preview/main.js'])
</head>
<body class="hx-preview">
    <div id="preview"></div>
    <script type="application/json" id="preview-data">
        {!! json_encode(['payload' => $payload, 'names' => $names, 'strings' => $strings], JSON_HEX_TAG | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR) !!}
    </script>
</body>
</html>
