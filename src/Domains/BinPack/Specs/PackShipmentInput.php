<?php

namespace App\Domains\BinPack\Specs;

class PackShipmentInput
{
    public function __construct(
        /** @var array<string, mixed> $items */
        public array $items = [],
        /** @var array<string, mixed> $bins */
        public array $bins = [],
    ) {
        //
    }
}
