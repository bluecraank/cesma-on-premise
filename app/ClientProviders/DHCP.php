<?php

namespace App\ClientProviders;

use App\Interfaces\IClient;

class DHCP implements IClient
{
    public function queryClientData(): Array {

        // Daten vom DHCP Server holen
        return [];
    }
}

?>