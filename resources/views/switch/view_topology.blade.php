<x-layouts.main>
    @foreach ($grouped as $key => $device)
            <h2 class="subtitle">{{ $devices[$key]->hostname }}</h2>
            @foreach ($device as $topology_switch)
                    <div>{{ $topology_switch['hostname'] }} - {{ $topology_switch['port_id'] }} - {{ $topology_switch['mac_address'] }}</div>
            @endforeach
            <br><br><br>
    @endforeach

    <div class="box">
        <h2 class="subtitle">Topologie</h2>
        <div style="height:1000px;" id="topology_map"></div>

    </div>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script type="text/javascript">
        // create an array with nodes
        var nodes = new vis.DataSet({!! $nodes !!});
      
        // create an array with edges
        var edges = new vis.DataSet([
          { from: 1, to: 3 },
          { from: 1, to: 2 },
          { from: 2, to: 4 },
          { from: 2, to: 5 },
          { from: 3, to: 3 }
        ]);
      
        // create a network
        var container = document.getElementById("topology_map");
        var data = {
          nodes: nodes,
          edges: edges
        };
        var options = {};
        var network = new vis.Network(container, data, options);
      </script>
    </x-layouts>
