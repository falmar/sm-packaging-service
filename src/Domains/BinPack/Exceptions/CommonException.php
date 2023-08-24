<?php

namespace App\Domains\BinPack\Exceptions;

class CommonException extends \Exception
{
    public string $errorCode = 'unknown_error';
    public string $errorMessage = 'An unknown error occurred.';

    /**
     * @param string $errorMessage
     * @return static
     */
    public static function make(string $errorMessage): static
    {
        // @phpstan-ignore-next-line
        $exception = new static($errorMessage);
        $exception->errorMessage = $errorMessage;
        return $exception;
    }
}
