<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of exception types with their corresponding custom log levels.
     *
     * @var array<class-string<\Throwable>, \Psr\Log\LogLevel::*>
     */
    protected $levels = [
        //
    ];

    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<\Throwable>>
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed to the session on validation exceptions.
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
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });
    }

    /**
     * Pastikan API selalu dapat respons JSON (untuk frontend Vue).
     */
    public function render($request, Throwable $e)
    {
        if ($request && ($request->expectsJson() || $request->is('api/*'))) {
            if ($e instanceof ValidationException) {
                return response()->json([
                    'message' => $e->getMessage(),
                    'errors' => $e->errors(),
                ], 422);
            }

            // Tangani DB driver tidak ditemukan agar front-end bisa memberitahu user jelas.
            if ($e instanceof \PDOException && str_contains($e->getMessage(), 'could not find driver')) {
                return response()->json([
                    'message' => 'Database driver tidak ditemukan (pdo_mysql). Jalankan: buka php.ini, aktifkan extension=pdo_mysql lalu restart server.',
                ], 503);
            }

            $code = 500;
            if ($e instanceof HttpException) {
                $code = $e->getStatusCode();
            }
            if ($code < 400) {
                $code = 500;
            }

            $message = 'Terjadi kesalahan server.';
            if (config('app.debug')) {
                $message = $e->getMessage();
            }
            return response()->json(['message' => $message], $code);
        }

        return parent::render($request, $e);
    }

}
