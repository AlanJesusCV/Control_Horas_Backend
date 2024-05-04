<?php

namespace App\Exceptions;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\UnauthorizedException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;
use Illuminate\Auth\AuthenticationException;

use function App\Helpers\formatErrorResponse;

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

    public function render($request, Throwable $exception)
    {
        if ($exception instanceof ModelNotFoundException) {
            return $this->handleModelNotFoundException($exception);
        } elseif ($exception instanceof ValidationException) {
            return $this->handleValidationException($exception);
        } elseif ($exception instanceof NotFoundHttpException) {
            return $this->handleNotFoundHttpException($exception);
        } elseif ($exception instanceof HttpException) {
            return $this->handleHttpException($exception);
        }else if ($exception instanceof UnauthorizedException) {
            return $this->handleUnauthorizedHttpException();
        }else if ($exception instanceof AuthenticationException) {
            return $this->handleUnauthorizedHttpException();
        }

        return parent::render($request, $exception);
    }


    protected function handleModelNotFoundException(ModelNotFoundException $exception)
    {
        $error = formatErrorResponse(true, 'Recurso no encontrado', []);
        return response()->json($error, 404);
    }

    protected function handleValidationException(ValidationException $exception)
    {
        $error = formatErrorResponse(true, $exception->validator->errors(), []);
        return response()->json($error, 422);
    }

    protected function handleNotFoundHttpException(NotFoundHttpException $exception)
    {
        $error = formatErrorResponse(true, 'Ruta no encontrada', []);
        return response()->json($error, 500);
    }

    protected function handleHttpException(HttpException $exception)
    {
        $error = formatErrorResponse(true, $exception->getMessage(), []);
        return response()->json($error, $exception->getStatusCode());
    }

    protected function handleUnauthorizedHttpException()
    {
        $error = formatErrorResponse(true, 'El token ya expiro o es invalido, intente iniciar sesion de nuevo. ', []);
        return response()->json($error,402);

    }

}
