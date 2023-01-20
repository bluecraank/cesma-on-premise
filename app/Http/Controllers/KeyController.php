<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\Key;
use Illuminate\Http\Request;

class KeyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    static function getPubkeysAsText()
    {
        $keys = Key::all();

        $keys2 = "";
        $i = 0;

        foreach ($keys as $key) {
            $format_key = explode(" ", EncryptionController::decrypt($key->key));

            $desc = $format_key[2] ?? "Imported";
            $correct = $desc . " " . $format_key[0] . " " . $format_key[1];
            $keys2 .= $correct . "\n";

            $i++;
        }

        return $keys2;
    }

    static function getPubkeysAsArray()
    {
        $keys = Key::all();

        $keys2 = [];
        $i = 1;

        foreach ($keys as $key) {
            $format_key = EncryptionController::decrypt($key->key);
            $keys2[$i] = $format_key;

            $i++;
        }

        return $keys2;
    }

    static function getPubkeysDesc()
    {
        $keys_db = Key::all();

        $keys = [];
        $i = 1;

        foreach ($keys_db as $key) {
            $keys[$i] = $key->description;

            $i++;
        }

        return $keys;
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreKeyRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
            'key' => 'required|string||starts_with:ssh-rsa|min:50',
        ])->validate();

        $store_key = EncryptionController::encrypt($request->input('key'));

        $key = new Key();
        $key->description = $request->description;
        $key->key = $store_key;
        $key->save();
        LogController::log('Pubkey erstellt', '{"description": "' . $key->description . '"}');

        return redirect()->back()->with('success', 'Key created successfully!');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Key  $key
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $key)
    {
        $key = Key::find($key->input('id'));
        if ($key) {
            $key->delete();
            LogController::log('Pubkey gelöscht', '{"description": "' . $key->description . '"}');
            return redirect()->back()->with('success', 'Key deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Key not found!');
        }
    }
}
