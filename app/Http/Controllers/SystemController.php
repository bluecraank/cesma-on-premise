<?php

namespace App\Http\Controllers;

use App\Models\MacTypeIcon;
use App\Models\PublicKey;
use App\Models\Router;
use App\Models\User;
use App\Models\Vlan;
use App\Models\VlanTemplate;
use App\Services\MacTypeService;
use App\Services\PublicKeyService;
use App\Services\VlanService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Helper\CLog;
use App\Models\Device;
use App\Models\DevicePort;
use App\Models\Permission;
use App\Models\Site;
use App\Models\Topology;

class SystemController extends Controller
{
    public function index_system()
    {
        $users = User::all();
        $permissions = Permission::all();
        $sites = Site::all();
        $keys = PublicKey::all();
        $keys_list = PublicKeyService::getPubkeysDescriptionAsArray();
        $mac_prefixes = MacTypeService::getMacTypes();
        $mac_types = MacTypeService::getMacTypesList();
        $mac_vendors = MacTypeService::getMacVendors();
        $mac_icons = MacTypeService::getMacIcons();
        $vlans = Vlan::all();
        $vlan_templates = VlanTemplate::all();
        $routers = Router::all();


        config(['app.ssh_private_key' => 'false']);

        return view('system.index', compact('sites', 'permissions', 'users', 'keys', 'keys_list', 'mac_prefixes', 'mac_types', 'mac_vendors', 'mac_icons', 'vlans', 'vlan_templates', 'routers'));
    }

    public function index_usersettings()
    {
        return view('system.view_usersettings');
    }

    public function index_topology()
    {
        $topology = Topology::all();
        $devices = Device::all();

        $nodes = [];
        $already_added = [];
        $edges = [];

        // Create nodes and edges for topology view
        foreach ($topology as $topo) {
            $name = $name2 = null;

            // Add note for local device if not already added
            if (!isset($already_added[$topo->local_device])) {
                $name = $devices->where('id', $topo->local_device)->first()?->named;
                if ($name) {
                    $nodes[] = [
                        'id' => $topo->local_device,
                        'label' => $name . "(".$topo->local_device.")",
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
                $name2 = $devices->where('id', $topo->remote_device)->first()?->named;
                if ($name2) {
                    $nodes[] = [
                        'id' => $topo->remote_device,
                        'label' => $name2 . "(".$topo->remote_device.")",
                        'shape' => 'box',
                        'margin' => 10,
                        'color' => [
                            'background' => '#ffffff',
                            'border' => '#ffffff',
                        ]
                    ];
                }
            }

            $already_added[$topo->local_device] = true;
            $already_added[$topo->remote_device] = true;

            if($topo->local_device == 0 || $topo->remote_device == 0 || $topo->local_port == 0 || $topo->remote_port == 0) {
                continue;
            }

            // Switch ports to prevent duplicate edges
            if($topo->local_device > $topo->remote_device) {
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
        foreach($edges as $edge) {
            $from_port = str_replace(["ethernet"], "", $edge['from_port']);
            $to_port = str_replace(["ethernet"], "", $edge['to_port']);
            $devices = Device::whereIn('id', [$edge['from'], $edge['to']])->get()->keyBy('id')->toArray();
            
            if($devices[$edge['from']]['type'] == 'aruba-cx') {
                $from_port = str_replace(["1/1/"], "", $edge['from_port']);
            }
            
            if($devices[$edge['to']]['type'] == 'aruba-cx') {
                $to_port = str_replace(["1/1/"], "", $edge['to_port']);
            }
            
            $get_from_device_port = DevicePort::where('device_id', $edge['from'])->where('name', $from_port)->first();
            $get_to_device_port = DevicePort::where('device_id', $edge['to'])->where('name', $to_port)->first();
            
            if($get_from_device_port->speed == 100) {
                $speed_color = "#f0ad4e";
            } elseif($get_from_device_port->speed == 1000) {
                $speed_color = "#5cb85c";
            } elseif($get_from_device_port->speed == 10000) {
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
                'chosen' => [
                    'label' => true,
                ],
                'font' => [
                    'align' => 'middle',
                ],
            ]);
        }

        if(count($new_edges) == 0) {
            $message = __('Msg.NoTopologyData');
        } else {
            $message = null;
        }
        
        $keys = array_column($new_edges, 'from');
        array_multisort($keys, SORT_ASC, $new_edges);
        $edges = $new_edges;
        return view('system.topology', compact('nodes', 'edges', 'message'));
    }
    
    public function updateUserRole(Request $request)
    {
        $guid = $request->input('guid');
        
        
        if ($guid == Auth::user()->guid) {
            return redirect()->back()->withErrors(['message' => __('Msg.Error.UserRoleUpdate')])->withInput(['last_tab' => 'users']);
        }

        $user = User::where('guid', $guid)->firstOrFail();
        if (!$user) {
            return redirect()->back()->withErrors(['message' => __('User.NotFound')])->withInput(['last_tab' => 'users']);
        }

        $user->role = $request['role'];
        if (!$user->save()) {
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'users']);
        }

        Permission::where('guid', $guid)->delete();
        if($request->has('sites')) {
            foreach ($request['sites'] as $site) {
                Permission::create([
                    'site_id' => $site,
                    'guid' => $guid,
                    'role' => $request['role'],
                ]);
            }
        }

        CLog::info("System", "Updated user {$user->name} to {$user->role}");

        return redirect()->back()->with('success', __('Msg.UserRoleUpdated'))->withInput(['last_tab' => 'users']);
    }

    public function updateTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:vlan_templates,id',
            'name' => 'required|string|max:255',
            'vlans_selected' => 'required|array|min:1',
        ])->validate();

        $template = VlanTemplate::where('id', $request->id)->firstOrFail();
        if (!$template) {
            return redirect()->back()->withErrors(['message' => __('VlanTemplate.NotFound')])->withInput(['last_tab' => 'vorlagen']);
        }

        $template->name = $request['name'];
        $template->vlans = json_encode($request['vlans_selected']);
        if (!$template->save()) {
            CLog::error("System", "Could not update vlan template {$request->name}");
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'vorlagen']);
        }

        CLog::info("System", "Update vlan template {$request->name}");
        return redirect()->back()->with('success', __('Msg.VlanTemplateUpdated'))->withInput(['last_tab' => 'vorlagen']);
    }

    public function deleteTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:vlan_templates,id',
        ])->validate();

        $template = VlanTemplate::where('id', $request->id)->firstOrFail();
        if (!$template) {
            return redirect()->back()->withErrors(['message' => __('VlanTemplate.NotFound')])->withInput(['last_tab' => 'vorlagen']);
        }

        if (!$template->delete()) {
            CLog::error("System", "Could not delete vlan template {$template->name}");
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'vorlagen']);
        }

        CLog::info("System", "Delete vlan template {$template->name}");
        return redirect()->back()->with('success', __('Msg.VlanTemplateDeleted'))->withInput(['last_tab' => 'vorlagen']);
    }

    public function storeRouter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip|unique:routers,ip',
            'desc' => 'nullable|string|max:255',
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
            CLog::info("System", "Create router {$request->ip}");
            return redirect()->back()->with('success', __('Msg.RouterCreated'))->withInput(['last_tab' => 'snmp']);
        } else {
            CLog::error("System", "Could not create router {$request->ip}");
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'snmp']);
        }
    }

    public function updateRouter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id' => 'required|integer|exists:routers,id',
            'ip' => 'required|string',
            'desc' => 'nullable|string|max:255',
        ])->validate();

        $router = Router::where('id', $request->id)->firstOrFail();
        if (!$router) {
            return redirect()->back()->withErrors(['message' => __('Router.NotFound')])->withInput(['last_tab' => 'snmp']);
        }

        try {
            $data = snmp2_real_walk($request->ip, 'public', '.1.3.6.1.2.1.4.22.1.2', 5000000, 1);
            if (count($data) > 1) {
                $check = true;
            } else {
                $check = false;
            }
        } catch (\Exception $e) {
            $check = false;
        }

        $router->desc = $request['desc'];
        $router->ip = $request['ip'];
        $router->check = $check;
        if (!$router->save()) {
            CLog::error("System", "Could not update router {$request->ip}");
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'snmp']);
        }

        CLog::info("System", "Update router {$request->ip}");
        return redirect()->back()->with('success', __('Msg.RouterUpdated'))->withInput(['last_tab' => 'snmp']);
    }

    public function deleteRouter(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ip' => 'required|ip',
        ])->validate();

        $router = Router::where('ip', $request->ip)->firstOrFail();
        if (!$router) {
            return redirect()->back()->withErrors(['message' => __('Router.NotFound')])->withInput(['last_tab' => 'snmp']);
        }

        if (!$router->delete()) {
            CLog::error("System", "Could not delete router {$request->ip}");
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'snmp']);
        }

        CLog::info("System", "Delete router {$request->ip}");
        return redirect()->back()->with('success', __('Msg.RouterDeleted'))->withInput(['last_tab' => 'snmp']);
    }
}
