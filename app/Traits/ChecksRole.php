<?php
    namespace App\Traits;

    use App\Helper\CLog;
    use Illuminate\Support\Facades\Auth;

    trait ChecksRole
    {
        // 0 = read-only user, 1 = administrator, 2 = super administrator
        protected function requireAdmin() : void {
            $this->requireRole(1);
        }

        protected function requireSuperAdmin() : void {
            $this->requireRole(2);
        }

        private function requireRole(int $role) : void {
            if((Auth::user()?->role ?? 0) < $role) {
                // Name the denied action, e.g. "ShowDevices::delete"
                $caller = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 3)[2]['function'] ?? '?';
                CLog::warning("Access", "Access denied: " . class_basename($this) . "::" . $caller, null, [
                    'role' => Auth::user()?->role,
                    'required_role' => $role,
                ]);
                abort(403);
            }
        }
    }
?>
