<?php

namespace App\Helper;

use App\Models\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log as FileLog;

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

    public static function write($category, $level, $description, $device = null, $additional_info = null, $user = null)
    {
        $console = app()->runningInConsole();
        // $user can be passed if the action happens before the user is set (e.g. login)
        $user = $user ?? ($console ? "Console" : (auth()->user()->name ?? "Unknown"));
        $additional_info = is_array($additional_info) ? json_encode($additional_info, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : $additional_info;
        $site_id = $device->site_id ?? self::currentSiteId();
        $ip = $console ? null : request()->ip();

        // Also write to the log file: it is kept independent of the database cleanup
        // and still works if the database is not reachable
        try {
            FileLog::log($level, "[{$category}] {$description}", array_filter([
                'user' => $user,
                'ip' => $ip,
                'device' => isset($device->name) ? "{$device->name} ({$device->id})" : null,
                'site_id' => $site_id,
                'info' => $additional_info,
            ], fn ($value) => $value !== null && $value !== ''));
        } catch (\Throwable $e) {
        }

        // Logging must never break the action that is logged
        try {
            $log = new Log();
            $log->category = $category;
            $log->level = $level;
            $log->user = $user;
            $log->user_id = auth()->user()->guid ?? null;
            $log->device_id = $device->id ?? null;
            $log->device_name = $device->name ?? null;
            $log->site_id = $site_id;
            $log->ip = $ip;
            $log->description = $description;
            $log->additional_info = $additional_info;
            $log->save();
        } catch (\Throwable $e) {
            FileLog::error("[CLog] Could not write log entry to the database: " . $e->getMessage());
        }
    }

    /**
     * Changed attributes of a model as "attribute" => ['old' => ..., 'new' => ...].
     * Call before save(). Secrets are only reported as changed.
     */
    public static function changes(Model $model, array $secret = ['password']): array
    {
        $changes = [];

        foreach ($model->getDirty() as $attribute => $new) {
            if (in_array($attribute, ['updated_at', 'created_at'])) {
                continue;
            }

            $changes[$attribute] = in_array($attribute, $secret)
                ? 'changed'
                : ['old' => $model->getOriginal($attribute), 'new' => $new];
        }

        return $changes;
    }

    private static function currentSiteId()
    {
        try {
            if (!app()->runningInConsole() && auth()->check()) {
                return auth()->user()->currentSite()?->id;
            }
        } catch (\Throwable $e) {
            // currentSite() aborts if the user has no site
        }

        return null;
    }
}
