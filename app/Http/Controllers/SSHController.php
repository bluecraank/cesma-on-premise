<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SSHController extends Controller
{
    public function overview()
    {
        return view('device.perform-ssh');
    }
}
