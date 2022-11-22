<x-layouts.header />
    <body>
        <div class="columns is-gapless">
            <x-layouts.menu />
            <div class="column">
                <div class="container is-fluid mt-6">
                    {{ $slot }}
                </div>s
            </div>
        </div>
    </body>
<x-layouts.footer />
