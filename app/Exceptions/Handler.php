<?php

namespace App\Exceptions;

use App\Http\Responses\ApiResponse;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\ThrottleRequestsException;
use Throwable;

class Handler extends ExceptionHandler
{
    use ApiResponse;

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
    }

    public function render($request, Throwable $e)
    {
        if (! ($request->expectsJson() || $request->is('api/*'))) {
            return parent::render($request, $e);
        }

        return match (true) {
            $e instanceof ValidationException => $this->validationError(
                errors: $e->errors()
            ),
            $e instanceof ModelNotFoundException => $this->notFound(
                'The requested resource was not found.'
            ),
            $e instanceof NotFoundHttpException => $this->notFound(
                'The requested endpoint was not found.'
            ),
            $e instanceof AuthenticationException => $this->error(
                401,
                'Authentication is required.'
            ),
            $e instanceof AuthorizationException => $this->error(
                403,
                $e->getMessage() ?: 'You are not allowed to perform this action.'
            ),
            $e instanceof MethodNotAllowedHttpException => $this->error(
                405,
                'This HTTP method is not allowed for the requested endpoint.'
            ),
            $e instanceof ThrottleRequestsException => $this->error(
                429,
                'Too many requests. Please try again later.'
            ),
            $e instanceof HttpExceptionInterface => $this->error(
                $e->getStatusCode(),
                $e->getMessage() ?: (Response::$statusTexts[$e->getStatusCode()] ?? 'Something went wrong.')
            ),
            default => $this->error(
                500,
                config('app.debug')
                    ? $e->getMessage()
                    : 'Internal server error.'
            ),
        };
    }
}
