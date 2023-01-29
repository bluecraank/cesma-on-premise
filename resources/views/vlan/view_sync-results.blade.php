<x-layouts.main>
    <div class="box">
        <div class="is-pulled-right">
            <form method="POST" onsubmit="$('.submit').addClass('is-loading')">
                @csrf 
                <input type="hidden" value="{{ $location_id }}" name="location_id" checked>
                <input type="hidden" value="{{ ($rename_vlans) ? 'on' : 'off' }}" name="overwrite-vlan-name">
                <input type="hidden" value="{{ ($create_vlans) ? 'on' : 'off' }}" name="create-if-not-exists" checked>
                <input type="hidden" value="off" name="test-mode" checked>
                <button class="button submit is-primary">Synchronisation starten</button>
            </form>
        </div>

        <h1 class="title">{{ __('Vlan.Sync.Title') }} {{ ($testmode) ? '(TEST)' : '' }}</h1>

        @foreach ($results as $key => $result)
            <article class="message">
                <div class="message-header">
                    <p>{{ $devices[$key]->name }} finished in {{ $result['time'] }} seconds
                    </p>
                    <button class="delete" aria-label="delete"></button>
                </div>
                <div class="message-body">
                    @foreach ($result['log'] as $msg)
                        <p>{!! $msg !!}</p>
                    @endforeach
                </div>
            </article>
        @endforeach

        </br>
        <b>{{ __('Vlan.Sync.Time', ['time' => number_format($elapsed, 2)]) }}</b>
    </div>
    </x-layouts>
