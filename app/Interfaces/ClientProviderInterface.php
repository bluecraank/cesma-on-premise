<?php 

    namespace App\Interfaces;
    
    interface ClientProviderInterface
    {
     
        /**
         * @return Array<Endpoint>
         */
        static function queryClientData(): Array; 
    }
?>