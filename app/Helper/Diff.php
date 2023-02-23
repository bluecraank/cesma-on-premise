<?php

namespace App\Helper;

use App\Models\Log;
use App\Models\Device;

class Diff
{
    public static function compare($old, $new)
    {
        $old = is_array($old) ? $old : $old->toArray();
        $new = is_array($new) ? $new : $new->toArray();
        $diff = [];
        foreach($old as $key => $value) {
            if(!isset($new[$key])) {
                continue;
            }

            if($value != $new[$key]) {
                if($key == 'password') {
                    $value = '********';
                }

                if($key == "updated_at" || $key == "created_at") {
                    continue;
                }

                $diff[$key] = [
                    'old' => $value,
                    'new' => $new[$key]
                ];
            }
        }
        return json_encode($diff, JSON_UNESCAPED_UNICODE);
    }

    public static function visualize($diff) {
        if($diff == null || json_decode($diff) == null || json_decode($diff) == []) {
            return $diff;
        }

        $diff = is_array($diff) ? $diff : json_decode($diff, true);
        $diff = self::changeUnwantedKeys($diff);
        $diff = json_encode($diff, JSON_PRETTY_PRINT);
        print("<pre>".$diff."</pre>");
    }

    public static function changeUnwantedKeys($dimension) {

        // Bei mehr Anwendungsfällen dieser Funktionen, sollte ein Array mitgegeben werden welche Keys nicht gewollt sind

        foreach($dimension as $key => $value) {
            if($key == "created_at" || $key == "updated_at" || $key == "id") {
                unset($dimension[$key]);
            }
            if($key == "device_id" | $key == "port_id") {
                unset ($dimension[$key]);
            }

            if($key == "password") {
                $dimension[$key] = "****";
            }

            if(is_array($value)) {
                $dimension[$key] = self::changeUnwantedKeys($value);
            }
        }

        return $dimension;
    }
}