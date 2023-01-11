<?php
    set_time_limit(0);

    // Ping clients just for ARP Cache in Gateway and switches
    // This script runs forever (cesma-scanner.service)

    while (true) {
            usleep(2000);
            
            $start = microtime(true);
    
            $subnets = [
                1 => '192.168.1.0/24',
                10 => '192.168.10.0/24',
                60 => '192.168.60.0/24',
                100 => '192.168.100.0/24',
                102 => '192.168.102.0/24',
                120 => '192.168.120.0/24',
                200 => '192.168.200.0/24',
                // 503 => '10.50.3.0/24',
                510 => '10.50.10.0/24',
                512 => '10.50.12.0/24',
                520 => '10.50.20.0/22',
                530 => '10.50.30.0/24',
                // 560 => '10.50.60.0/24',
            ];
    
            foreach($subnets as $net) {
                echo "Scanning ".$net."\n";
                $result = exec("nmap -sn ".$net." -oX storage/app/".explode("/", $net)[0], $output, $return);
            }
    
            echo "Took: ".(microtime(true)-$start)."sec\n";
            sleep(120);
    }

?>