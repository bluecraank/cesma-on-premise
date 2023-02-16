<?php

namespace App\Services;

use App\Http\Controllers\LogController;
use App\Models\PublicKey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class PublicKeyService
{
    static function storePublicKey(Request $request) {
    
        PublicKey::create([
            'description' => $request->description,
            'key' => Crypt::encrypt($request->input('key')),
        ]);

        // LogController::log('Pubkey erstellt', '{"description": "' . $request->description . '"}');
    }

    static function getPublicKeysAsArray()
    {
        $keys = PublicKey::all();

        $keys_decrypted = [];
        $i = 1;

        foreach ($keys as $key) {
            $format_key = Crypt::decrypt($key->key);
            $keys_decrypted[$i] = $format_key;

            $i++;
        }

        return $keys_decrypted;
    }

    static function getPubkeysDescriptionAsArray()
    {
        $keys = PublicKey::all();

        $keys_desc = [];
        $i = 1;

        foreach ($keys as $key) {
            $keys_desc[$i] = $key->description;

            $i++;
        }

        return $keys_desc;
    }

    static function getPubkeysAsFile()
    {
        $keys = PublicKey::all();

        $keys_raw = "";
        $i = 0;

        foreach ($keys as $key) {
            $format_key = explode(" ", Crypt::decrypt($key->key));
            $desc = $format_key[2] ?? "Imported";
            $correct = $desc . " " . $format_key[0] . " " . $format_key[1];

            $keys_raw .= $correct . "\n";
            $i++;
        }

        return $keys_raw;
    }
}



?>