<?php

namespace App\Domains\BinPack\Exceptions;

class PackagingNotFound extends CommonException
{
    public string $errorCode = 'packaging_not_found';
    public string $errorMessage = 'No packaging found for the given products.';
}
