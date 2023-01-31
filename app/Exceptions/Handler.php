<?php

namespace App\Exceptions;

use Illuminate\Database\QueryException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
        $this->reportable(function (\Throwable $e) {
            //
        });

        $this->renderable(function (\Throwable $exception, $request) {
            if ($request->is('api/*')) {
                if ($exception instanceof NotFoundHttpException) {
                    return response()->json([
                        'code' => $exception->getStatusCode(),
                        'message' => 'Endpoint not found'
                    ], $exception->getStatusCode());
                } elseif ($exception instanceof ModelNotFoundException) {
                    $message = $exception->getMessage();
                    if ($exception->getModel()) $message = last(explode('\\', $exception->getModel())) . ' not found';
                    return response()->json([
                        'code' => $exception->getCode() ?: 404,
                        'message' => $message
                    ], $exception->getCode() ?: 404);
                } elseif ($exception instanceof ValidationException) {
                    return response()->json([
                        'code' => 422,
                        'message' => 'The given data was invalid',
                        'errors' => $exception->errors()
                    ], 422);
                } elseif ($exception instanceof AuthenticationException) {
                    return response()->json([
                        'code' => 401,
                        'message' => $exception->getMessage()
                    ], 401);
                } elseif ($exception instanceof QueryException) {
                    return response()->json([
                        'code' => 500,
                        'message' => $exception->getMessage(),
                    ], 500);
                } elseif ($exception instanceof \Exception) {
                    return response()->json([
                        'code' => $exception->getCode() ?: 500,
                        'message' => $exception->getMessage()
                    ], $exception->getCode() ?: 500);
                }
            }
        });
    }
}
