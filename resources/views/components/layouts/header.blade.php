<!DOCTYPE html>
<html lang="de">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="shortcut icon" href="/img/favicon.png" type="image/x-icon">
    <link rel="stylesheet" href="/css/bulma.min.css">
    <link rel="stylesheet" href="" id="theme">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">
    <link rel="stylesheet" href="/css/multi-select.css">
    <link rel="stylesheet" href="/css/cesma.css">

    <script src="/js/jquery-3.6.0.min.js"></script>
    <script src="/js/jquery.multi-select.js"></script>

    @livewireStyles
    @livewireScripts

    @if (Route::is('details'))
        <script src="/js/api_ports.js"></script>
    @endif

    @if (Auth::user() && Auth::user()->role >= 1) 
        <script src="/js/api.js"></script>
    @endif

    <script src="/js/notify.min.js?{{ config('app.version') }}"></script>
    <script src="/js/functions.js?{{ config('app.version') }}"></script>
    <script src="/js/theme.js?{{ config('app.version') }}"></script>

    <title>@yield('title') | CESMA</title>
</head>
<noscript>
    <div class="no-use">
        <div class="notification is-danger always-visible is-radiusless">
            {!! __('Misc.NoScript') !!}
        </div>
    </div>
</noscript>
