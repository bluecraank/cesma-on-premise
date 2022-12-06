<?php
    namespace App\Traits;

    use Illuminate\Support\Facades\Auth;

    trait WithLogin
    {
        protected function checkLogin() : void {
            if(!Auth::check()) {
                abort(403);
            }
        }
    }
?>