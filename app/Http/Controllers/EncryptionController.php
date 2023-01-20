<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;

class EncryptionController extends Controller
{
    static function encrypt($password)
    {
        $secret = config('app.encryption');

        $cipher = "aes-128-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($password, $cipher, $secret, $options = OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $secret, $as_binary = true);

        $encrypted = base64_encode($iv . $hmac . $ciphertext_raw);


        return $encrypted;
    }

    static function decrypt($data)
    {
        $secret = config('app.encryption');
        $c = base64_decode($data);

        $cipher = "aes-128-cbc";
        $ivlen = openssl_cipher_iv_length($cipher);
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_plaintext = openssl_decrypt($ciphertext_raw, $cipher, $secret, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $secret, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) {
            return $original_plaintext;
        }
    }

    static function getPrivateKey()
    {
        $privateKey = Storage::disk('local')->get('ssh.key');

        $decrypted = EncryptionController::decrypt($privateKey);

        return $decrypted;
    }
}
