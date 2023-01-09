<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">Trunks</h1>
    
        <div class="is-pulled-right ml-4">
            
        </div>
    
        <div class="is-pulled-right">

        </div>
    
        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Switch</th>
                    <th>Trunks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data as $device)
                <tr>
                    <td>{{ $device['name'] }}</td>
                    <td>{!! implode(', ', $device['trunks']) !!}</td>
                </tr>
                @endforeach
        </table>
    
        <span  class='has-text-link has-size-7'>ArubaCX: Ports mit allen VLANs tagged</span>
    </div>
</x-layouts>
