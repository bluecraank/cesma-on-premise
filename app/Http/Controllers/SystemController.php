<?php

namespace App\Http\Controllers;

use App\Models\Key;
use Illuminate\Http\Request;

class SystemController extends Controller
{
    public function index_system()
    {
        $keys = Key::all();
        $keys2 = [];

        $keys_list = KeyController::getPubkeysDesc();


        foreach ($keys as $k => $key) {
            $keys2[$k] = new \stdClass();
            $keys2[$k]->desc = $key->description;
            $keys2[$k]->key = EncryptionController::decrypt($key->key);
            $keys2[$k]->id = $key->id;
        }

        $keys = $keys_list;

        return view('system.index', compact('keys2', 'keys'));
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
}
