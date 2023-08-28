@section('title', 'Topology')

<x-layouts.main>


        <div class="card">
            <header class="card-header">
                <p class="card-header-title">
                    <span class="icon"><i class="mdi mdi-map"></i></span>
                    {{ __('Topology') }}
                </p>

                <div class="mr-5 in-card-header-actions">

                </div>
            </header>

            <div class="card-content p-3">
                @if (!isset($edges) || count($edges) == 0)
                    <div style="width:100%;height:600px;display:flex;align-items:center;justify-content:center;"><b
                            class="is-size-4">{{ __('No topology data found') }}</b></div>
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
        </div>

    <script type="text/javascript">
        // ...
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
          physics: {
            enabled: true,
            barnesHut: {
              gravitationalConstant: -2000,
              centralGravity: 0.3,
              springLength: 165,
              springConstant: 0.04,
              damping: 0.09,
              avoidOverlap: 1
            }
          },
        };
        var network = new vis.Network(container, data, options);
      </script>
    </x-layouts>
