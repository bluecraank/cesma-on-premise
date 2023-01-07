<?php 

    namespace App\Interfaces;
    
    interface IClient
    {
     
        /**
         * @return Array<Endpoint>
         */
        public function queryClientData(): Array; 


    }
?>