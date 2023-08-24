<?php

namespace App\Domains\BinPack\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class Packaging implements \JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(type: Types::FLOAT)]
    private float $width;

    #[ORM\Column(type: Types::FLOAT)]
    private float $height;

    #[ORM\Column(type: Types::FLOAT)]
    private float $length;

    #[ORM\Column(type: Types::FLOAT)]
    private float $maxWeight;

    public function __construct(float $width, float $height, float $length, float $maxWeight)
    {
        $this->width = $width;
        $this->height = $height;
        $this->length = $length;
        $this->maxWeight = $maxWeight;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): Packaging
    {
        $this->id = $id;
        return $this;
    }

    public function getWidth(): float
    {
        return $this->width;
    }

    public function setWidth(float $width): Packaging
    {
        $this->width = $width;
        return $this;
    }

    public function getHeight(): float
    {
        return $this->height;
    }

    public function setHeight(float $height): Packaging
    {
        $this->height = $height;
        return $this;
    }

    public function getLength(): float
    {
        return $this->length;
    }

    public function setLength(float $length): Packaging
    {
        $this->length = $length;
        return $this;
    }

    public function getMaxWeight(): float
    {
        return $this->maxWeight;
    }

    public function setMaxWeight(float $maxWeight): Packaging
    {
        $this->maxWeight = $maxWeight;
        return $this;
    }

    public function getVolume(): float
    {
        return $this->width * $this->height * $this->length;
    }

    /**
     * @return array<string, mixed>
     */
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'width' => $this->width,
            'height' => $this->height,
            'length' => $this->length,
            'max_weight' => $this->maxWeight,
        ];
    }
}
