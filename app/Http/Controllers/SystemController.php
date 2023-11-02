<?php

namespace App\Http\Controllers;

use App\Models\MacTypeIcon;
use App\Models\PublicKey;
use App\Models\Router;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;
use App\Models\Client;
use App\Models\Device;
use App\Models\DevicePort;
use App\Models\MacType;
use App\Models\Topology;
use App\Models\Vlan;
use App\Services\ChartService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class SystemController extends Controller
{
    public function dashboard()
    {
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get();
        // $vlans = Vlan::all()->count();
        $vlans = Vlan::where('site_id', Auth::user()->currentSite()->id)->get();
        // $clients = Client::all()->count();
        $clients = Client::where('site_id', Auth::user()->currentSite()->id)->get();
        // $ports = DevicePort::all()->count();
        $ports = DevicePort::whereIn('device_id', $devices->pluck('id'))->get();

        $portsToVlans = ChartService::portsToVlans($devices, $vlans);
        $clientsToVlans = ChartService::clientsToVlans($clients, $vlans);
        $portsOnline = ChartService::portsOnline($ports);
        $devicesOnline = ChartService::devicesOnline($devices);

        $ports = $ports->count();
        $vlans = $vlans->count();
        $clients = $clients->count();

        $notifications = \App\Models\Notification::where('site_id', Auth::user()->currentSite()->id)->where('type', '!=', 'uplink')->orderBy('updated_at', 'DESC')->take(10)->get();
        // $notifications = \App\Models\Notification::where('site_id', Auth::user()->currentSite()->id)->orderBy('updated_at', 'DESC')->get();

        return view('dashboard', compact('notifications', 'portsToVlans', 'clientsToVlans', 'portsOnline', 'devicesOnline', 'clients', 'vlans', 'ports'));
    }

    public function index_usersettings()
    {
        return view('system.view_usersettings');
    }

    public function index_snmp() {
        $routers = Router::all();

        return view('system.index_snmp', compact('routers'));
    }

    public function index_mac_type() {
        $mac_types = MacType::all();
        $mac_types_unique = $mac_types->unique('type');
        $mac_type_icons = MacTypeIcon::all();

        return view('system.index_mac_types', compact('mac_types', 'mac_type_icons', 'mac_types_unique'));
    }

    public function index_publickeys() {
        $publickeys = PublicKey::all();

        return view('system.index_publickeys', compact('publickeys'));
    }

    public function index_privatekey() {
        $privatekey = Storage::disk('local')->get('ssh.key');

        return view('system.index_privatekey', compact('privatekey'));
    }

    public function index_topology()
    {
        $topology = Topology::all();
        $devices = Device::where('site_id', Auth::user()->currentSite()->id)->get();

        $nodes = [];
        $already_added = [];
        $edges = [];

        // Create nodes and edges for topology view
        foreach ($topology as $topo) {
            $name = $name2 = null;

            if ($topo->local_device == 0 || $topo->remote_device == 0 || $topo->local_port == 0 || $topo->remote_port == 0) {
                continue;
            }

            // Add note for local device if not already added
            if (!isset($already_added[$topo->local_device])) {
                $name = $devices->where('id', $topo->local_device)->first()?->name;
                if ($name) {
                    $nodes[] = [
                        'id' => $topo->local_device,
                        'label' => $name,
                        'shape' => 'box',
                        'margin' => 10,
                        'color' => [
                            'background' => '#ffffff',
                            'border' => '#000',
                        ]
                    ];
                }
            }

            // Add note for remote device if not already added
            if (!isset($already_added[$topo->remote_device])) {
                $name2 = $devices->where('id', $topo->remote_device)->first()?->name;
                if ($name2) {
                    $nodes[] = [
                        'id' => $topo->remote_device,
                        'label' => $name2,
                        'shape' => 'box',
                        'margin' => 10,
                        'color' => [
                            'background' => '#ffffff',
                            'border' => '#000000',
                        ]
                    ];
                }
            }

            $already_added[$topo->local_device] = true;
            $already_added[$topo->remote_device] = true;


            // Switch ports to prevent duplicate edges
            if ($topo->local_device > $topo->remote_device) {
                $lowerDevice = $topo->local_device;
                $higherDevice = $topo->remote_device;
                $lowerPort = $topo->local_port;
                $higherPort = $topo->remote_port;
            } else {
                $lowerDevice = $topo->remote_device;
                $higherDevice = $topo->local_device;
                $lowerPort = $topo->remote_port;
                $higherPort = $topo->local_port;
            }

            // Skip if edge already exists
            if (array_search([
                'from' => $lowerDevice,
                'to' => $higherDevice,
                'from_port' => $lowerPort,
                'to_port' => $higherPort,
            ], $edges) === false) {

                $edges[] = [
                    'from' => $lowerDevice,
                    'to' => $higherDevice,
                    'from_port' => $lowerPort,
                    'to_port' => $higherPort,
                ];
            }
        }

        $options = [
            'smooth' => [
                'enabled' => false,
            ],
        ];


        $new_edges = [];
        foreach ($edges as $edge) {
            $from_port = str_replace(["ethernet"], "", $edge['from_port']);
            $to_port = str_replace(["ethernet"], "", $edge['to_port']);
            $devices = Device::whereIn('id', [$edge['from'], $edge['to']])->get()->keyBy('id')->toArray();

            if ($devices[$edge['from']]['type'] == 'aruba-cx') {
                $from_port = str_replace(["1/1/"], "", $edge['from_port']);
            }

            if ($devices[$edge['to']]['type'] == 'aruba-cx') {
                $to_port = str_replace(["1/1/"], "", $edge['to_port']);
            }

            $get_from_device_port = DevicePort::where('device_id', $edge['from'])->where('name', $from_port)->first();
            $get_to_device_port = DevicePort::where('device_id', $edge['to'])->where('name', $to_port)->first();

            if(!$get_from_device_port) {
                $get_from_device_port = new DevicePort();
                $get_from_device_port->speed = 0;
            }

            if ($get_from_device_port->speed == 100) {
                $speed_color = "#f0ad4e";
            } elseif ($get_from_device_port->speed == 1000) {
                $speed_color = "#5cb85c";
            } elseif ($get_from_device_port->speed == 10000) {
                $speed_color = "#3a743a";
            } else {
                $speed_color = "#d9534f";
            }

            $temp = array_merge($edge, $options);
            $new_edges[] = array_merge($temp, [
                'color' => [
                    'color' => $speed_color,
                ],
                'label' => $from_port . " - " . $to_port,
                'font' => [
                    'align' => 'middle',
                ],
                'width' => 4,
                'title' => $devices[$edge['from']]['name'] . ':' . $from_port . ' to ' . $devices[$edge['to']]['name'] . ':' . $to_port
            ]);
        }

        $keys = array_column($new_edges, 'from');
        array_multisort($keys, SORT_ASC, $new_edges);
        $edges = $new_edges;
        return view('system.topology', compact('nodes', 'edges'));
    }

    public function createGateway(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip|unique:routers,ip',
            'desc' => 'required|string|max:255',
        ])->validate();

        try {
            $data = snmp2_real_walk($request->ip, 'public', '.1.3.6.1.2.1.4.22.1.2', 5000000, 1);
            if ($data) {
                $check = true;
            } else {
                $check = false;
            }
        } catch (\Exception $e) {
            $check = false;
        }

        $status = Router::create([
            'ip' => $request['ip'],
            'desc' => $request['desc'],
            'check' => $check,
        ]);

        if ($status) {
            CLog::info("Gateways", "Router created", null, $request->ip . " (". $request->desc .")");
            return redirect()->back()->with('success', __('Succesfully added a new gateway for client discovery'))->withInput(['last_tab' => 'snmp']);
        } else {
            return redirect()->back()->withErrors(['message' => __('Something went wrong')])->withInput(['last_tab' => 'snmp']);
        }
    }

    public function deleteGateway(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
            'id' => 'required|integer|exists:routers,id',
        ])->validate();

        $router = Router::where('id', $request->id)->firstOrFail();
        if (!$router) {
            return redirect()->back()->withErrors(['message' => __('This gateway coult not be found')]);
        }

        if (!$router->delete()) {
            return redirect()->back()->withErrors(['message' => __('Something went wrong')]);
        }

        CLog::info("Gateways", "Gateway {$request->desc} deleted");
        return redirect()->back()->with('success', __('Gateway :desc successfully deleted', ['desc' => $request['ip']]));
    }
}
