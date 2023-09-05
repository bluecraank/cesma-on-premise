<?php

namespace App\Http\Controllers;

use App\Models\Notification;

class NotificationController extends Controller
{
    static function uplink($port, $device, $data, $type, $reason) {
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
            'unique-identifier' => $type.'-' . $device->id . '-' . $port,
            'device_id' => $device->id,
            'site_id' => $device->site_id,
        ],
        [
            'title' => $title,
            'message' => $message,
            'data' => json_encode($data),
            'type' => $type,
        ]);

    }

    static function link_change($port, $device, $new_link) {
        $new_link = $new_link ? "UP" : "DOWN";
        $title = $device->name;
        $message = "Port ".$port." changed link to ".$new_link.".";

        Notification::updateOrCreate([
            'unique-identifier' => 'link-change-' . $device->id . '-' . $port . '-' . $new_link,
            'device_id' => $device->id,
            'site_id' => $device->site_id,
        ],
        [
            'title' => $title,
            'message' => $message,
            'data' => json_encode([]),
            'type' => 'link-change',
        ]);
    }

    static function speed_change($port, $device, $new_speed, $old_speed) {
        $title = $device->name;
        $message = "Port ".$port." changed speed from ".$old_speed." Mbit to ".$new_speed." Mbit.";

        Notification::updateOrCreate([
            'unique-identifier' => 'speed-change-' . $device->id . '-' . $port . '-' . $new_speed,
            'device_id' => $device->id,
            'site_id' => $device->site_id,
        ],
        [
            'title' => $title,
            'message' => $message,
            'data' => json_encode([]),
            'type' => 'link-change',
        ]);
    }
}
