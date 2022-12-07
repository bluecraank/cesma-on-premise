<x-layouts.header />

<body>
    <div class="columns is-gapless">
        @if(!Route::is('login'))
        <x-layouts.menu />
        @endif
        <div class="column">
            <div class="container is-fluid mt-6">
                @if ($errors->any())
                <div class="notification status is-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @endif

                @if(session()->has('success'))
                <div class="notification status is-success">
                    {{ session()->get('success') }}
                </div>
                @endif
                
                {{ $slot }}
            </div>
        </div>
    </div>
</body>
@if(!Route::is('login'))
<x-layouts.footer />
@endif