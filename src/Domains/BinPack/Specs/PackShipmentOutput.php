<?php

namespace App\Domains\BinPack\Specs;

use App\Domains\BinPack\ValueObjects\API\Bin;

class PackShipmentOutput
{
    /** @var Bin[] */
    public array $bins = [];
}
