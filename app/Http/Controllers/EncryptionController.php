<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Crypt;

class EncryptionController extends Controller
{
    static function encrypt($password)
    {
        $encrypted = Crypt::encryptString($password);
        return $encrypted;
    }

    static function decrypt($data)
    {
        $decrypted = Crypt::decryptString($data);
        return $decrypted;
    }

    static function getPrivateKey()
    {
        $privateKey = Storage::disk('local')->get('ssh.key');

        $decrypted = EncryptionController::decrypt($privateKey);

        return $decrypted;
    }
}
