<?php 

    namespace App\Interfaces;
    
    interface IClientProvider
    {
     
        /**
         * @return Array<Endpoint>
         */
        static function queryClientData(): Array; 

        static function queryClientDataDebug(): Array;
    }
?>