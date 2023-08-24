<?php

namespace App\Domains\BinPack\Exceptions;

/**
 * Class ApiErrorException
 *
 * basic http errors given by the api
 */
class ApiErrorException extends \Exception
{
    public string $errorCode = 'api_error';
    public string $errorMessage = 'An error occurred while communicating with the BinPack API.';

    public static function make(string $code, string $errorMessage): self
    {
        $exception = new ApiErrorException($errorMessage);
        $exception->errorCode = $code;
        $exception->errorMessage = $errorMessage;
        return $exception;
    }
}
