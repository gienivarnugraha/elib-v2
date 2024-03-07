<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.bunny.net/css?family=Nunito" rel="stylesheet">

    <!-- Scripts -->
    @vite(['resources/js/app.js'])

    <script>
        var config = {!! Js::from(array_merge($config, ['csrfToken' => csrf_token()])) !!};
    </script>
</head>

<body>
    <div id="app" class="h-screen flex overflow-hidden bg-neutral-100 dark:bg-neutral-800" v-cloak>

    </div>
</body>

</html>
