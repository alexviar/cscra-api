<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
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
        if ($request->wantsJson()) {   //add Accept: application/json in request
            return $this->handleApiException($request, $exception);
        } else {
            $retval = parent::render($request, $exception);
        }

        return $retval;
    }
    
    /**
     * Convert a validation exception into a JSON response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Illuminate\Validation\ValidationException  $exception
     * @return \Illuminate\Http\JsonResponse
     */
    protected function invalidJson($request, $exception)
    {
        return response()->json([
            'message' => "Los datos proporcionados fueron invalidos.",
            'errors' => $exception->errors(),
        ], $exception->status);
    }

    private function handleApiException($request, Throwable $exception)
    {
        $exception = $this->prepareException($exception);
        if (method_exists($exception, 'render') && $response = $exception->render($request)) {
          return $response;
        }

        // if ($exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
        //     $exception = $exception->getResponse();
        // }

        // if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
        //     $exception = $this->unauthenticated($request, $exception);
        // }

        // if ($exception instanceof \Illuminate\Validation\ValidationException) {
        //     $exception = $this->convertValidationExceptionToResponse($exception, $request);
        // }

        // return $this->customApiResponse($exception);

        if ($exception instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
            return $exception->getResponse();
        }
        if ($exception instanceof \Illuminate\Auth\AuthenticationException) {
            return $this->unauthenticated($request, $exception);
        }
        if ($exception instanceof \Illuminate\Validation\ValidationException) {
            return $this->convertValidationExceptionToResponse($exception, $request);
        }
        return $this->customApiResponse($exception);
    }

    private function customApiResponse($exception)
    {
        if (method_exists($exception, 'getStatusCode')) {
            $statusCode = $exception->getStatusCode();
        } else {
            $statusCode = 500;
        }

        $response = [];

        switch ($statusCode) {
            case 401:
                $response['message'] = 'Usuario no autenticado';
                break;
            case 403:
                $response['message'] = 'No tiene permiso para acceder a esta función';
                break;
            case 404:
                $response['message'] = $exception->getMessage() ?: 'Not Found';
                break;
            case 405:
                $response['message'] = 'Method Not Allowed';
                break;
            // case 422:
            //     $response['message'] = "Los datos proporcionados fueron invalidos.";
            //     $response['errors'] = $exception->original['errors'];
            //     break;
            default:
                $response['message'] = ($statusCode == 500 && !config('app.debug')) ? 'A ocurrido un error inesperado en el servidor.' : $exception->getMessage();
                break;
            // default:
            //     $response['message'] = $exception->getMessage();
        }

        if (config('app.debug')) {
            if(method_exists($exception, "getTrace")) $response['trace'] = $exception->getTrace();
            if(method_exists($exception, "getCode")) $response['code'] = $exception->getCode();
        }

        $response['status'] = $statusCode;

        return response()->json($response, $statusCode);
    }


}
