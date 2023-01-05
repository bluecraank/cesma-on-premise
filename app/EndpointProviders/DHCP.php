<?php

namespace App\EndpointProviders;

use App\Interfaces\IEndpoint;

class DHCP implements IEndpoint
{
    public function queryClientData(): Array {

        // Daten vom DHCP Server holen
        return [];
    }
}

?>