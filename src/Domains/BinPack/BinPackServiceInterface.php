<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\PackagingNotFound;
use App\Domains\BinPack\ValueObjects\Product;

interface BinPackServiceInterface
{
    /**
     * Obtain the smallest box that can fit the given products.
     *
     * @param Product[] $products
     * @return Packaging
     * @throws PackagingNotFound
     */
    public function getSmallestBoxForProducts(array $products): Packaging;
}
