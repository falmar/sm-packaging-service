<?php

namespace App\Domains\BinPack\Exceptions;

/**
 * Class ApiErrorException
 *
 * basic http errors given by the api
 */
class ApiErrorException extends CommonException
{
    public string $errorCode = 'api_error';
    public string $errorMessage = 'An error occurred while communicating with the BinPack API.';
}
