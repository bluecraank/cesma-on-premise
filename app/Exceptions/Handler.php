<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     */
    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Audit log for denied requests (Livewire actions are logged in the ChecksRole trait)
        $this->renderable(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, $request) {
            if ($e->getStatusCode() == 403 && auth()->check() && !$request->is('livewire/*')) {
                \App\Helper\CLog::warning("Access", "Access denied: " . $request->method() . " /" . $request->path(), null, [
                    'role' => auth()->user()->role,
                ]);
            }

            // Continue with the default rendering
            return null;
        });
    }
}
