<x-layouts.main>
    <div class="box">
        <h1 class="title is-pulled-left">Uplinks</h1>
    
        <div class="is-pulled-right ml-4">
            
        </div>
    
        <div class="is-pulled-right">

        </div>
    
        <table class="table is-narrow is-hoverable is-striped is-fullwidth">
            <thead>
                <tr>
                    <th>Switch</th>
                    <th>Uplinks</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($devices as $device)
                <tr>
                    <td>{{ $device['name'] }}</td>
                    @php    
                        $uplinks = json_decode($device['uplinks'], true);
                        if($uplinks == null or empty($uplinks) or !$uplinks) {
                            $uplinks = array();
                        }
                        $uplinks = implode(', ', $uplinks);
                    @endphp
                    <td>{{ $uplinks }}</td>

                </tr>
                @endforeach
        </table>
        </div>
</x-layouts>
