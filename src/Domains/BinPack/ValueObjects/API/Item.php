<?php

namespace App\Domains\BinPack\ValueObjects\API;

class Item implements \JsonSerializable
{
    public string|int $id;
    public float $width;
    public float $height;
    public float $length;
    public float $weight;
    public int $quantity = 1;
    public bool $verticalRotation = true;

    public function __construct(
        string|int $id,
        float $width,
        float $height,
        float $length,
        float $weight,
        int $quantity = 1,
        bool $verticalRotation = true
    ) {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->weight = $weight;
        $this->quantity = $quantity;
        $this->verticalRotation = $verticalRotation;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'w' => $this->width,
            'h' => $this->height,
            'd' => $this->length,
            'wg' => $this->weight,
            'q' => $this->quantity,
            'vr' => $this->verticalRotation,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }
}
