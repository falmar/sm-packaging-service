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

    /**
     * @param string $hash
     * @return Packaging
     * @throws PackagingNotFound
     */
    public function getCachedPackaging(string $hash): Packaging;

    /**
     * @param string $hash
     * @param Packaging $packaging
     * @return void
     */
    public function saveCachedPackaging(string $hash, Packaging $packaging): void;
}
