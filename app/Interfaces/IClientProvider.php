<?php 

    namespace App\Interfaces;
    
    interface IClientProvider
    {
     
        /**
         * @return Array<Endpoint>
         */
        static function queryClientData(): Array; 
    }
?>