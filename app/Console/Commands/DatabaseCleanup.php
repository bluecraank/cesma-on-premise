<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\DeviceBackup;
use App\Models\DevicePort;
use App\Models\DevicePortStat;
use App\Models\DeviceUplink;
use App\Models\Log;
use App\Models\Mac;
use App\Models\Vlan;
use App\Models\SnmpMacData;
use Illuminate\Console\Command;

class DatabaseCleanup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'database:cleanup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up the database';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // Number of deleted rows per kind, logged at the end
        $deleted = [];

        $deleted['old_macs'] = ($deleted['old_macs'] ?? 0) + Mac::whereDate('created_at', '<=', now()->subWeek(1))->delete();
        $deleted['stale_clients'] = ($deleted['stale_clients'] ?? 0) + Client::whereDate('updated_at', '<=', now()->subWeek(8))->delete();
        $deleted['old_backups'] = ($deleted['old_backups'] ?? 0) + DeviceBackup::whereDate('created_at', '<=', now()->subYear(2))->delete();
        $deleted['old_port_stats'] = ($deleted['old_port_stats'] ?? 0) + DevicePortStat::whereDate('created_at', '<=', now()->subWeek(2))->delete();
        $deleted['old_logs'] = ($deleted['old_logs'] ?? 0) + Log::whereDate('created_at', '<=', now()->subWeek(8))->delete();
        $deleted['old_snmp_mac_data'] = ($deleted['old_snmp_mac_data'] ?? 0) + SnmpMacData::whereDate('updated_at', '<=', now()->subWeek(4))->delete();
        $deleted['old_notifications'] = ($deleted['old_notifications'] ?? 0) + \App\Models\Notification::whereDate('created_at', '<=', now()->subWeek(8))->where('status', '!=', "declined")->delete();
        $deleted['old_link_notifications'] = ($deleted['old_link_notifications'] ?? 0) + \App\Models\Notification::whereDate('created_at', '<=', now()->subWeek(1))->where('type', "link-change")->delete();

        // Note: an empty where closure matches every row, so whereIn/whereNotIn with explicit lists are used here
        $sites = \App\Models\Site::all()->keyBy('id')->toArray();

        foreach($sites as $site) {
            // Delete clients where the vlan does not belong to the site (anymore).
            // This also covers vlans of other sites, vlan ids are only unique per site.
            $siteVlanIds = Vlan::where('site_id', $site['id'])->pluck('vid')->toArray();
            $deleted['clients_wrong_vlan'] = ($deleted['clients_wrong_vlan'] ?? 0) + Client::where('site_id', $site['id'])->whereNotIn('vlan_id', $siteVlanIds)->delete();
        }

        $ports = DevicePort::get()->groupBy('device_id')->toArray();
        foreach($ports as $device_id => $device_ports) {
            $deleted['clients_unknown_port'] = ($deleted['clients_unknown_port'] ?? 0) + Client::where('device_id', $device_id)->whereNotIn('port_id', array_column($device_ports, 'name'))->delete();
        }

        // Delete clients without vlan and clients in vlans which are no client vlans (per site)
        $deleted['clients_without_vlan'] = ($deleted['clients_without_vlan'] ?? 0) + Client::where('vlan_id', 0)->delete();
        foreach (Vlan::where('is_client_vlan', 0)->get() as $vlan) {
            $deleted['clients_non_client_vlan'] = ($deleted['clients_non_client_vlan'] ?? 0) + Client::where('site_id', $vlan->site_id)->where('vlan_id', $vlan->vid)->delete();
        }

        $array_uplinks = [];
        $uplinks = DeviceUplink::all()->keyBy('id')->groupBy('device_id')->toArray();
        foreach($uplinks as $dev_id => $uplink) {
            foreach($uplink as $each_uplink) {
                $array_uplinks[$dev_id][] = $each_uplink['name'];
            }
        }

        foreach($array_uplinks as $device_id => $uplinks) {
            $deleted['clients_on_uplinks'] = ($deleted['clients_on_uplinks'] ?? 0) + Client::where('device_id', $device_id)->whereIn('port_id', $uplinks)->delete();
        }


        \Illuminate\Support\Facades\Log::info('[System] Database cleaned up', array_filter($deleted));
    }
}
