<x-layouts.header />
    <body>
        <div class="columns is-gapless">
            @if(!Route::is('login'))
                <x-layouts.menu />
            @endif
            <div class="column">
                <div class="container is-fluid mt-6">
                    {{ $slot }}
                </div>
            </div>
        </div>
    </body>
@if(!Route::is('login'))
    <x-layouts.footer />
@endif