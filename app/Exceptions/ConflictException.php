<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Validation\ValidationException;

class ConflictException extends Exception
{
    private $data;
    /**
     * Report the exception.
     *
     * @return bool|null
     */
    public function report()
    {
        //
    }

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function render($request)
    {
        return response()->json([
          "message" => $this->getMessage(),
          "payload" => $this->data
        ], 409);
    }

    public static function withData($data){
      $exception = new ConflictException("Conflict");
      $exception->data = $data;
      return $exception;
    }
}