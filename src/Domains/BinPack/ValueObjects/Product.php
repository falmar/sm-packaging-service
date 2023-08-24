<?php

namespace App\Domains\BinPack\ValueObjects;

class Product
{
    public function __construct(
        public string|int $id,
        public float $width,
        public float $height,
        public float $length,
        public float $weight,
    ) {
        //
    }
}
