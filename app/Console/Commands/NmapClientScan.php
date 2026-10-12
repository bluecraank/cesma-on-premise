<?php

namespace App\Console\Commands;

use App\Models\Vlan;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class NmapClientScan extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'clients:nmap-scan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping clients of scanned vlans to fill the ARP cache of gateways and switches';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $start = microtime(true);

        foreach (Vlan::where('is_scanned', true)->whereNotNull('ip_range')->get() as $vlan) {
            $ip_range = trim($vlan->ip_range);

            // Only plain IPv4 networks in CIDR notation are passed to the shell
            if (!self::isValidCidr($ip_range)) {
                Log::warning('[NmapClientScan] Invalid ip range "' . $ip_range . '" for vlan ' . $vlan->vid . ', skipped');
                continue;
            }

            $this->line("Scanning " . $ip_range);
            exec("nmap -sn " . escapeshellarg($ip_range), $output, $return);
        }

        $this->line("Took: " . round(microtime(true) - $start, 2) . "sec");

        return Command::SUCCESS;
    }

    static function isValidCidr(string $ip_range): bool
    {
        $parts = explode('/', $ip_range);

        if (count($parts) != 2 || !filter_var($parts[0], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
            return false;
        }

        return ctype_digit($parts[1]) && (int) $parts[1] >= 8 && (int) $parts[1] <= 32;
    }
}
