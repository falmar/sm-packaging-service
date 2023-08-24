<?php

namespace App\Domains\BinPack\ValueObjects\API;

class Bin implements \JsonSerializable
{
    public string|int $id;
    public float $width;
    public float $height;
    public float $length;
    public float $maxWeight;

    // defaults
    public float $weight = 0;
    public int $quantity = 1;
    public ?float $cost = null;
    public string $type = 'pallet';
    public float $usedVolume = 0;

    public function __construct(
        string|int $id,
        float $width,
        float $height,
        float $length,
        float $maxWeight,

        float $weight = 0,
        int $quantity = 1,
        ?float $cost = null,
        string $type = 'pallet',
        float $usedVolume = 0
    ) {
        $this->id = $id;
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->maxWeight = $maxWeight;

        $this->weight = $weight;
        $this->quantity = $quantity;
        $this->cost = $cost;
        $this->type = $type;
        $this->usedVolume = $usedVolume;
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
            'max_wg' => $this->maxWeight,

            'wg' => $this->weight,
            'q' => $this->quantity,
            'cost' => $this->cost,
            'type' => $this->type,
            'used_volume' => $this->usedVolume,
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
