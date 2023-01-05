<?php 

    namespace App\Interfaces;
    
    interface IEndpoint
    {
     
        /**
         * @return Array<Endpoint>
         */
        public function queryClientData(): Array; 


    }
?>