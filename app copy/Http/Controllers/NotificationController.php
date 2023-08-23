<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    static function new($port, $device, $data, $type, $reason) {
        $title = "";
        $message = "Port ".$port." of device ".$device->name." (".$device->hostname.") has been detected as uplink. Reason: ";

        switch($type) {
            case "uplink":
                $title = "Uplink detected";
                break;
        }

        switch($reason) {
            case "vlans":
                $message .= $data['vlans']." Vlans detected";
                break;
            case "clients":
                $message .= $data['clients']." Clients detected";
                break;
            case "trunk":
                $message .= "Trunk detected";
                break;
            case "topology":
                $message .= "Entry in topology detected";
                break;
        }

        Notification::updateOrCreate([
            'unique-identifier' => $type.'-' . $device->id . '-' . $port
        ],
        [
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'type' => $type,
        ]);

    }
}
