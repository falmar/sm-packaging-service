<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\BinPack\Specs\PackShipmentOutput;

interface BinPackApiInterface
{
    /**
     * @param PackShipmentInput $input
     * @return PackShipmentOutput
     * @throws ApiErrorException
     */
    public function packShipment(PackShipmentInput $input): PackShipmentOutput;
}
