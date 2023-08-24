<?php

namespace App\Domains\BinPack\Exceptions;

class CommonException extends \Exception
{
    public string $errorCode = 'unknown_error';
    public string $errorMessage = 'An unknown error occurred.';

    /**
     * @param string $errorMessage
     * @return self
     */
    public static function make(string $errorMessage): self
    {
        $exception = new CommonException($errorMessage);
        $exception->errorMessage = $errorMessage;
        return $exception;
    }
}
