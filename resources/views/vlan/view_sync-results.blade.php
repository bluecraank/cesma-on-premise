<x-layouts.main>
    <div class="box">
        <h1 class="title">VLAN Synchronisation: Ergebnisse</h1>
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
        <b>Synchronisiert in {{ number_format($elapsed, 2) }} Sekunden</b>
    </div>
</x-layouts>