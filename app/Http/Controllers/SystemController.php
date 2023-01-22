<?php

namespace App\Http\Controllers;

use App\Models\Key;
use App\Models\MacTypeFilter;
use App\Models\MacTypeIcon;
use App\Models\MacVendors;
use App\Models\User;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function index_system()
    {
        $keys_db = Key::all();
        $keys = [];
        $users = User::all();
        $keys_list = KeyController::getPubkeysDesc();
        $macs = MacTypeFilter::all()->sortBy('mac_type');
        $types = MacTypeFilter::all()->sortBy('mac_type')->pluck('mac_type')->unique();
        $vendors = MacVendors::all()->keyBy('mac_prefix');
        $icons = MacTypeIcon::all()->keyBy('mac_type');

        foreach ($keys_db as $k => $key) {
            $keys[$k] = new \stdClass();
            $keys[$k]->desc = $key->description;
            $keys[$k]->key = EncryptionController::decrypt($key->key);
            $keys[$k]->id = $key->id;
        }

        return view('system.index', compact('keys', 'keys_list', 'users', 'macs', 'types', 'vendors', 'icons'));
    }

    public function index_usersettings()
    {
        return view('system.view_usersettings');
    }

    public function updateTheme(Request $request)
    {
        if ($request->input('theme')) {
            setcookie('theme', $request->input('theme'), time() + (86400 * 30), "/"); // 86400 = 1 day
        }

        return redirect()->back()->with('success', 'Theme updated!');
    }

    public function updateUserRole(Request $request)
    {
        $guid = $request->input('guid');
        $user = User::where('guid', $guid)->firstOrFail();
        if(!$user) {
            return redirect()->back()->withErrors(['message' => 'User not found']);
        }

        $user->role = ($request['role'] == 0) ? 'user' : 'admin';
        if(!$user->save()) {
            return redirect()->back()->withErrors(['message' => 'User could not be updated']);;
        }
        
        return redirect()->back()->with('success', 'User role updated!');
    }
}
