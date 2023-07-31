<x-layouts.main>

  <div class="box">
    <h1 class="title is-pulled-left">{{ __('Topology') }}</h1>

    <div class="is-pulled-right ml-4">

    </div>
        <div style="height:1000px;" id="topology_map"></div>

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
        var options = {};
        var network = new vis.Network(container, data, options);
      </script>
    </x-layouts>
