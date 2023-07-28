<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Validator;
use App\Models\PublicKey;
use App\Services\PublicKeyService;
use Illuminate\Http\Request;
use App\Helper\CLog;

class PublicKeyController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string|max:50',
            'key' => 'required|string||starts_with:ssh-rsa|min:50',
        ])->validate();

        PublicKeyService::storePublicKey($request);
        CLog::info("Pubkey", "Create pubkey {$request->description}", null, substr($request->key, 0, 20));
        return redirect()->back()->with('success', __('Msg.PubkeyCreated'))->withInput(['last_tab' => 'pubkeys']);
    }

    public function destroy(Request $key)
    {
        $key = PublicKey::find($key->input('id'));

        if ($key) {
            $key->delete();
            CLog::info("Pubkey", "Delete pubkey {$key->description}");
            return redirect()->back()->with('success', __('Msg.PubkeyDeleted'))->withInput(['last_tab' => 'pubkeys']);
        } else {
            CLog::error("Pubkey", "Could not delete pubkey {$key->description}");
            return redirect()->back()->with('message', 'Key not found!')->withInput(['last_tab' => 'pubkeys']);
        }
    }
}
