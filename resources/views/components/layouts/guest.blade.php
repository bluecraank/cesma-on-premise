<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>cesma</title>
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/scss/main.scss'])
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">

<body class="p-0 m-0">
    <div id="app">
        <main class="py-4">
            @yield('content')
        </main>
    </div>
</body>

</html>
