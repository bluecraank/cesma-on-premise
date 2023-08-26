<!DOCTYPE html>
<html lang="de" class="has-aside-left has-aside-mobile-transition has-navbar-fixed-top has-aside-expanded">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', $title ?? ucfirst(Route::currentRouteName())) | {{ Auth::user()->currentSite()->name }} | cesma</title>

    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/scss/main.scss'])
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script type="text/javascript" src="/js/jquery-3.6.0.min.js"></script>
    <script type="text/javascript" src="/js/jquery.notify.min.js"></script>
    <script type="text/javascript" src="/js/notify.js"></script>
    <script type="text/javascript" src="/js/multiselect.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="dns-prefetch" href="https://fonts.gstatic.com">
<body>
    <div id="app">
