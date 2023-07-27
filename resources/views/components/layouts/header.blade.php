<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="/img/favicon.png?{{ config('app.version') }}" type="image/x-icon">
    <link rel="stylesheet" href="/css/bulma.min.css?{{ config('app.version') }}">
    <link rel="stylesheet" href="" id="theme">

    <script src="/js/jquery-3.6.0.min.js?{{ config('app.version') }}"></script>
    <script src="/js/theme.js?{{ config('app.version') }}"></script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="/css/multi-select.css?{{ config('app.version') }}">
    <link rel="stylesheet" href="/css/cesma.css?{{ config('app.version') }}">
    
    <script src="/js/jquery.multi-select.js?{{ config('app.version') }}"></script>
    <script src="/js/multiselect.js?{{ config('app.version') }}"></script>

    @livewireStyles
    @livewireScripts

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @if (Route::is('show-device'))
        <script src="/js/api_ports.js?{{ config('app.version') }}"></script>
    @endif

    @guest
        <title>@yield('title') | CESMA</title>
    @endguest

    @auth
        <title>@yield('title') | {{ Auth::user()?->currentSite()->name ?? '' }} | CESMA</title>
    @endauth
</head>
<noscript>
    <div class="no-use">
        <div class="notification is-danger always-visible is-radiusless">
            {!! __('Misc.NoScript') !!}
        </div>
    </div>
</noscript>
