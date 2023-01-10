<?php 

    namespace App\Interfaces;
    
    interface IClient
    {
     
        /**
         * @return Array<Endpoint>
         */
        static function queryClientData(): Array; 


    }
?>