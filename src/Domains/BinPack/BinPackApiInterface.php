<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\BinPack\Specs\PackShipmentOutput;
use App\Domains\BinPack\ValueObjects\API\Bin;
use App\Domains\BinPack\ValueObjects\API\Item;
use App\Domains\BinPack\ValueObjects\Product;

interface BinPackApiInterface
{
    /**
     * @param PackShipmentInput $input
     * @return PackShipmentOutput
     * @throws ApiErrorException
     */
    public function packShipment(PackShipmentInput $input): PackShipmentOutput;

    /**
     * @param Product[] $products
     * @return Item[]
     */
    public function parseItemsFromProduct(array $products): array;

    /**
     * @param Packaging[] $packages
     * @return Bin[]
     */
    public function parseBinsFromPackaging(array $packages): array;
}
