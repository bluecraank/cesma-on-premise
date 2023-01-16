<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLogRequest;
use App\Http\Requests\UpdateLogRequest;
use App\Models\Log;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function index()
    {
        $logs = Log::all()->sortByDesc('created_at');
        return view('log.index', compact('logs'));
    }

    static function log($msg, $data = null)
    {
        $log = new Log();
        $log->message = $msg;
        $log->data = $data;
        $log->user = Auth::user()->id;
        $log->save();
    }
}
