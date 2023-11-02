@section('title', __('SSH Privatekey'))

<x-layouts.main>
    <div class="card">
        <header class="card-header">
            <p class="card-header-title">
                <span class="icon"><i class="mdi mdi-key"></i></span>
                {{ __('SSH Privatekey') }}
            </p>
        </header>

        <div class="card-content p-3">
            @if($privatekey !== null)
            <div class="notification is-danger">
                {{ __('CESMA already has a private key! Only change if you know what you do!') }}
            </div>
            @endif

            <label class="label">Enter privatekey</label>
            <form action="{{ route('create-private-key') }}" method="POST">
                @csrf
                <textarea class="textarea" type="text" name="key" placeholder="-----BEGIN RSA PRIVATE KEY-----
ABCDEF
-----END RSA PRIVATE KEY-----"></textarea> <hr>
                <button type="submit" class="no-prevent button is-info">Save private key into storage</button>
            </form>
        </div>
    </div>
{{--
    @include('modals.publickeys.create')
    @include('modals.publickeys.delete') --}}
    </x-layouts>
