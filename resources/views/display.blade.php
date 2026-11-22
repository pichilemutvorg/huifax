@php
    $title = config('huifax.branding.title');
    $tagline = config('huifax.branding.tagline');
@endphp

<!doctype html>
<html lang="{{ config('huifax.display.locale') }}" data-theme="dark">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>{{ $tagline ? __('display.document_title', ['title' => $title, 'tagline' => $tagline]) : $title }}</title>
    @vite (['resources/css/display.css', 'resources/js/display/main.js'])
</head>
<body>
    <div
        id="display"
        class="h-full w-full"
        data-locale="{{ config('huifax.display.locale') }}"
        data-timezone="{{ config('app.timezone') }}"
    ></div>
</body>
</html>
