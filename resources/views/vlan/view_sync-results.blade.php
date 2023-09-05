@section('title', 'Result of Vlan Sync')

<x-layouts.main>
    <div class="box">
        <div class="is-pulled-right">
            <form method="POST" >
                @csrf 
                <input type="hidden" value="{{ $site_id }}" name="site_id" checked>
                <input type="hidden" value="{{ ($rename_vlans) ? 'on' : 'off' }}" name="overwrite-vlan-name">
                <input type="hidden" value="{{ ($create_vlans) ? 'on' : 'off' }}" name="create-if-not-exists">
                <input type="hidden" value="{{ ($tag_to_uplink) ? 'on' : 'off' }}" name="tag-vlan-to-uplink">
                <input type="hidden" value="off" name="test-mode" checked>
                <button class="button submit is-primary">{{ __('Sync.Start') }}</button>
            </form>
        </div>

        <h1 class="title">{{ __('Vlan.Sync.Title') }} {{ ($testmode) ? '(TEST)' : '' }}</h1>

        @foreach ($results as $key => $result)
            <article class="message">
                <div class="message-header">
                    <p><i class="fa fa-bars-progress mr-2"></i> {{ $devices[$key]->name }}
                    </p>
                </div>
                <div class="message-body">
                    <table class="table is-bordered is-fullwidth">
                        <thead>
                            <tr>
                                <th>VLAN</th>
                                <th>Muss erstellt werden</th>
                                <th>Muss umbenannt werden</th>
                                <th>Muss auf Uplinks getagged werden</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php 
                                uksort($result, function ($a, $b) {
                                    return strnatcmp($a, $b);
                                });
                            @endphp
                            @foreach ($result as $vid => $msg)
                            <tr>
                                <td>{{ $vid }} ({{ $msg['name'] }})</td>
                                <td class="{{ (isset($msg['created'])) ? ($msg['created']) ? "has-text-success" : "has-text-danger" : "" }}">{{ (isset($msg['created'])) ? ($msg['created']) ? "Erstellt" : "Fehlgeschlagen" : "" }}</td>
                                <td class="{{ (isset($msg['changed'])) ? ($msg['changed']) ? "has-text-success" : "has-text-danger" : "" }}">{{ (isset($msg['changed'])) ? ($msg['changed']) ? $msg['old'] . " => " . $msg['name'] : "Fehlgeschlagen" : "" }}</td>
                                <td class="{{ (isset($msg['uplinks'])) ? ($msg['uplinks']) ? "has-text-success" : "has-text-danger" : "" }}">{{ (isset($msg['uplinks'])) ? ($msg['uplinks']) ? implode(", ", $msg['uplinks']) : "Fehlgeschlagen" : "" }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endforeach
    </div>
    </x-layouts>
