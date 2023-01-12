<?php
    set_time_limit(0);

    // Ping clients just for ARP Cache in Gateway and switches
    // This script runs forever (cesma-scanner.service)            
    $start = microtime(true);

    try {
        $db = new MySQLi('localhost', 'root', 'mQK62mNSgeoutu6G', 'cesma');
    } catch (Exception $e) {
        echo "Could not connect to database: ".$e->getMessage()."\n";
        die();
    }
    
    while (true) {
        usleep(2000);

        $result = $db->query("SELECT * FROM `vlans` WHERE `scan` = 1");
        $subnets = $result->fetch_all(MYSQLI_ASSOC);
        
        foreach($subnets as $net) {
            $ip = $net['ip_range'];
            echo "Scanning ".$ip."\n";
            $result = exec("nmap -sn ".$ip, $output, $return);
            // echo "Result: ".$return."\n";
        }

        echo "Took: ".(microtime(true)-$start)."sec\n";
        sleep(300);
    }

?>