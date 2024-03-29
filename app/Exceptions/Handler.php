<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Laravel\Lumen\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var array
     */
    protected $dontReport = [
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        ValidationException::class,
        NotFoundHttpException::class,
    ];

    /**
     * Report or log an exception.
     *
     * This is a great spot to send exceptions to Sentry, Bugsnag, etc.
     *
     * @param  \Exception  $exception
     * @return void
     */
    public function report(Exception $exception)
    {
        parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Exception  $exception
     * @return \Illuminate\Http\Response
     */
    public function render($request, Exception $e)
    {
        if($request->expectsJson()){
            if($e instanceof ValidationException ){
                return response()->json([
                    'message'=>$e->getMessage(),
                    'errors'=>$e->validator->errors()
                ], 422);
            }
        }

        switch (get_class($e)) {
            case OAuthServerException::class:
                return response()->json([
                    'status' => $e->getHttpStatusCode(),
                    'message' => $e->getMessage(),
                ], $e->getHttpStatusCode());
        }

        if($e instanceof NotFoundHttpException){
            return response()->json([
                'status' => 404,
                'message' => 'Not Found!',
            ], 404);
        }

        if($e instanceof \Illuminate\Auth\AuthenticationException ){
            return response()->json((['status' => 401, 'message' => 'Unauthorized']), 401);
        }

         if ($e instanceof AuthorizationException) {
            return response()->json((['status' => 403, 'message' => 'Insufficient privileges to perform this action']), 403);
        }

        if ($e instanceof ModelNotFoundException) {
            return response()->json(['status' => 404, 'message' => 'Not found'], 404);
        }

        if ($e instanceof MethodNotAllowedHttpException) {
            return response()->json((['status' => 405, 'message' => 'Method Not Allowed']), 405);
        }

        return parent::render($request, $e);
    }
}
