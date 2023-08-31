<?php

namespace App\Helper;

use App\Models\Log;

class CLog
{
    public static function info($category, $description, $device = null, $additional_info = null)
    {
        self::write($category, 'info', $description, $device, $additional_info);
    }

    public static function warning($category, $description, $device = null, $additional_info = null)
    {
        self::write($category, 'warning', $description, $device, $additional_info);
    }

    public static function error($category, $description, $device = null, $additional_info = null)
    {
        self::write($category, 'error', $description, $device, $additional_info);
    }

    public static function debug($category, $description, $device = null, $additional_info = null)
    {
        self::write($category, 'debug', $description, $device, $additional_info);
    }

    public static function write($category, $level, $description, $device = null, $additional_info = null)
    {

        $user = auth()->user()->name ?? "Unknown";
        if(app()->runningInConsole()) {
            $user = "Console";
        }
        $log = new Log();
        $log->category = $category;
        $log->level = $level;
        $log->user = $user;
        $log->user_id = auth()->user()->guid ?? null;
        $log->device_id = $device->id ?? null;
        $log->device_name = $device->name ?? null;
        $log->description = $description;
        $log->additional_info = is_array($additional_info) ? json_encode($additional_info, true) : $additional_info;
        $log->save();
    }
}
