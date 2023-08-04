@section('title', 'Topology')

<x-layouts.main>

  <div class="box">
    <h1 class="title is-pulled-left">{{ __('Topology') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>
        @if($message)
          <div style="width:100%;height:600px;display:flex;align-items:center;justify-content:center;"><b class="is-size-4">{{ $message }}</b></div>
        @else
          <div id="topology_map"></div>
        @endif
        <div>
          <span class="tag is-danger">10 Mbit/s</span>
          <span class="tag is-warning">100 Mbit/s</span>
          <span class="tag is-success">1000 Mbit/s</span>
          <span style="background-color: #3a743a" class="tag is-success">10000 Mbit/s</span>

        </div>
    </div>
    <script type="text/javascript" src="https://unpkg.com/vis-network/standalone/umd/vis-network.min.js"></script>
    <script type="text/javascript">
        // create an array with nodes
        var nodes = new vis.DataSet(@json($nodes));
      
        // create an array with edges
        var edges = new vis.DataSet(@json($edges));
      
        // create a network
        var container = document.getElementById("topology_map");
        var data = {
          nodes: nodes,
          edges: edges
        };
        var options = {
          width: '100%',
          height: '600px',
          layout: {
            randomSeed: 203,
          },
          'physics': {
            'barnesHut': {
              'springLength': 300,
            }
          }
        };
        var network = new vis.Network(container, data, options);
      </script>
    </x-layouts>
