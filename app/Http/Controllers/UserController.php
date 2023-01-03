<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\User;

class UserController extends Controller
{
    public function index()
    {
        $users = User::all();
        $key = Auth::user()->privatekey;
        if($key) {
            $privatekey = true;
        } else {
            $privatekey = false;
        }
        return view('user.index', compact('users', 'privatekey'));
    }

    public function management()
    {
        return view('user.management');
    }

    function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:50',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return redirect()->back()->withErrors($validator)->withInput();
        }

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->save();
        LogController::log('User erstellt', '{"name": "' . $user->name . '", "email": "' . $user->email . '"}');

        return redirect()->back()->with('success', 'User created successfully!');
    }

    function update(Request $request) {

        if($request->input('theme')) {
            setcookie('theme', $request->input('theme'), time() + (86400 * 30), "/"); // 86400 = 1 day
        }

        if(!empty($request->input('current_password'))) {
            $validator = Validator::make($request->all(), [
                'current_password' => 'min:1',
                'new_password' => 'min:8',
                'new_password_confirm' => 'same:new_password|min:8',
            ])->validate();
        }
        if(!empty($request->input('current_password'))) {
            if(Hash::check($request->input('current_password'), Auth::user()->password)) {
                $user = User::find(Auth::user()->id);
                $user->password = Hash::make($request->input('new_password'));
                $user->save();
            } else {
                return redirect()->back()->withErrors(['error' => 'Das aktuelle Passwort ist falsch.']);
            }
        }

        $key = $request->input('privatekey');
        $passphrase = $request->input('passphrase');
        if (!empty($key)) {
            $user = User::find(Auth::user()->id);
            $user->privatekey = EncryptionController::encryptKey($key, $passphrase);
            $user->save();
        }

        return redirect()->back()->with('success', 'User updated!');

    }

    function destroy(Request $request) {
        $curUser = Auth::user()->id;
        if($curUser == $request->input('id')) {
            return redirect()->back()->withErrors(['error' => 'Du kannst dich nicht selbst löschen.']);
        }

        $user = User::find($request->input('id'));
        $user->delete();
        LogController::log('User gelöscht', '{"name": "' . $user->name . '", "id": "' . $user->id . '", "email": "' . $user->email . '"}');

        return redirect()->back()->with('success', 'User deleted!');

    }

    function setPrivatekey(Request $request) {
        $user = User::find(Auth::user()->id);
        $privatekey = $request->input('privatekey');
        $passphrase = $request->input('passphrase');

        $validator = Validator::make($request->all(), [
            'privatekey' => 'required|min:100|starts_with:-----BEGIN RSA PRIVATE KEY-----|ends_with:-----END RSA PRIVATE KEY-----',
            'passphrase' => 'required|min:8|max:50',
        ])->validate();

        $key = EncryptionController::encryptKey($privatekey, $passphrase);
        $user->privatekey = $key;

        if($user->save()) {
            return redirect()->back()->with('success', 'Private Key gespeichert!');
        } else {
            return redirect()->back()->withErrors(['error' => 'Fehler beim Speichern des Private Keys.']);
        }
    }

    function deletePrivatekey() {
        $user = User::find(Auth::user()->id);
        $user->privatekey = null;
        if($user->save()) {
            return redirect()->back()->with('success', 'Private Key gelöscht!');
        } else {
            return redirect()->back()->withErrors(['error' => 'Fehler beim Löschen des Private Keys.']);
        }
    }
}
