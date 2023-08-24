<?php

namespace App\Domains\BinPack\Entities;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "packaging_cache")]
class CachedPackaging
{
    #[ORM\Id]
    #[ORM\Column(type: Types::INTEGER)]
    #[ORM\GeneratedValue]
    private ?int $id = null;

    #[ORM\Column(name: "input_hash", type: Types::STRING)]
    private string $inputHash;

    #[ORM\ManyToOne(targetEntity: Packaging::class)]
    #[ORM\JoinColumn(name: "packaging_id", referencedColumnName: "id")]
    private Packaging $packaging;

    #[ORM\Column(name: "expired_at", type: Types::DATETIME_MUTABLE)]
    private \DateTimeInterface $expiredAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): CachedPackaging
    {
        $this->id = $id;
        return $this;
    }

    public function getInputHash(): string
    {
        return $this->inputHash;
    }

    public function setInputHash(string $inputHash): CachedPackaging
    {
        $this->inputHash = $inputHash;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getPackaging(): Packaging
    {
        return $this->packaging;
    }

    /**
     * @param mixed $packaging
     * @return CachedPackaging
     */
    public function setPackaging(Packaging $packaging): self
    {
        $this->packaging = $packaging;
        return $this;
    }

    public function getExpiredAt(): \DateTimeInterface
    {
        return $this->expiredAt;
    }

    public function setExpiredAt(\DateTimeInterface $expiredAt): CachedPackaging
    {
        $this->expiredAt = $expiredAt;
        return $this;
    }

    // Getters, Setters and other methods
}
