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

class SystemController extends Controller
{
    public function index_system()
    {
        $users = User::all();
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

        return view('system.index', compact('users', 'keys', 'keys_list', 'mac_prefixes', 'mac_types', 'mac_vendors', 'mac_icons', 'vlans', 'vlan_templates', 'routers'));
    }

    public function index_usersettings()
    {
        return view('system.view_usersettings');
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

        return redirect()->back()->with('success', __('Msg.UserRoleUpdated'))->withInput(['last_tab' => 'users']);
    }

    public function storeTemplate(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:vlan_templates,name',
            'vlans_selected' => 'required|array|min:1',
        ])->validate();

        $status = VlanService::createVlanTaggingTemplate($request->all());

        if ($status) {
            return redirect()->back()->with('success', __('Msg.VlanTemplateCreated'))->withInput(['last_tab' => 'vorlagen']);
        } else {
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'vorlagen']);
        }
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
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'vorlagen']);
        }

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
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'vorlagen']);
        }

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
            return redirect()->back()->with('success', __('Msg.RouterCreated'))->withInput(['last_tab' => 'snmp']);
        } else {
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
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'snmp']);
        }

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
            return redirect()->back()->withErrors(['message' => __('Msg.SomethingWentWrong')])->withInput(['last_tab' => 'snmp']);
        }

        return redirect()->back()->with('success', __('Msg.RouterDeleted'))->withInput(['last_tab' => 'snmp']);
    }
}
