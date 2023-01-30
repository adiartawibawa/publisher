<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title inertia>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap">

    <!-- Highlight JS sheets -->
    <script src="//cdn.jsdelivr.net/gh/highlightjs/cdn-release@9.13.1/build/highlight.min.js"></script>

    <!-- Scripts -->
    @routes


    {{-- dev only --}}
    @production
        @php
            $manifest = json_decode(File::get(public_path('build/manifest.json')), true);
        @endphp
        <script type="module" src="{{ asset('build/' . $manifest['resources/js/app.js']['file']) }}"></script>
        <link rel="stylesheet" href="{{ asset('build/' . $manifest['resources/js/app.js']['css'][0]) }}">
    @else
        @verbatim
            <script type="module" src="http://localhost:5173/@vite/client"></script>
        @endverbatim
        <script type="module" src="http://localhost:5173/resources/js/app.js"></script>
    @endproduction

    {{-- for production only  --}}
    {{-- {{ vite_assets() }} --}}

    {{-- <script src="{{ mix('js/app.js') }}" defer></script> --}}


    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>
