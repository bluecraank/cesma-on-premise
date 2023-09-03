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
        return redirect()->back()->with('success', __('SSH public key created'));
    }

    public function destroy(Request $key)
    {
        $key = PublicKey::find($key->input('id'));

        if ($key) {
            $key->delete();
            CLog::info("Pubkey", "SSH public key {$key->description} deleted");
            return redirect()->back()->with('success', __('Successfully deleted ssh public key :name', ['name' => $key->description]));
        } else {
            return redirect()->back()->with('message', 'SSH public key not found');
        }
    }
}
