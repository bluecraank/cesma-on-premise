<x-layouts.header />
<x-layouts.menu />

<section class="section is-main-section">
    @if ($errors->any())
        <div class="notification is-response is-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    @if (session()->has('success'))
        <div class="notification is-response is-success">
            {{ session()->get('success') }}
        </div>
    @endif
    {{ $slot }}
</section>


<x-layouts.footer />
