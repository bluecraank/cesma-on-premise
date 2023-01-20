<?php

namespace App\Http\Controllers;

use App\Models\Log;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::all()->sortByDesc('created_at');
        return view('log.log-overview', compact('logs'));
    }

    static function log($msg, $data = null)
    {
        $user = Auth::user()->name;
        $log = new Log();
        $log->message = $msg;
        $log->data = $data;
        $log->user = '' . $user . '';
        $log->save();
    }
}
