<?php
    namespace App\Traits;

    use Illuminate\Support\Facades\Auth;

    trait ChecksRole
    {
        // 0 = read-only user, 1 = administrator, 2 = super administrator
        protected function requireAdmin() : void {
            if((Auth::user()?->role ?? 0) < 1) {
                abort(403);
            }
        }

        protected function requireSuperAdmin() : void {
            if((Auth::user()?->role ?? 0) < 2) {
                abort(403);
            }
        }
    }
?>
