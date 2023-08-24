<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Entities\CachedPackaging;
use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Exceptions\PackagingNotFound;
use App\Domains\BinPack\Specs\API\PackShipmentInput;
use App\Domains\BinPack\ValueObjects\Dimensions;
use App\Domains\BinPack\ValueObjects\Product;
use Doctrine\ORM\EntityManagerInterface;

class BinPackService implements BinPackServiceInterface
{
    private BinPackApiInterface $api;
    private EntityManagerInterface $entityManager;

    public function __construct(
        BinPackApiInterface $api,
        EntityManagerInterface $entityManager
    ) {
        $this->api = $api;
        $this->entityManager = $entityManager;
    }

    /**
     * @inheritDoc
     */
    public function getSmallestPackaging(array $products): Packaging
    {
        if (!$products) {
            throw new \InvalidArgumentException('No products found');
        }

        $dimensions = $this->getDimensions($products);
        $suitablePackaging = $this->getSuitablePackagingByDimensions($dimensions);

        if (!$suitablePackaging) {
            throw PackagingNotFound::make(
                'No suitable packaging found for the given products'
            );
        }

        try {
            $bins = $this->api->packShipment(
                new PackShipmentInput(
                    items: $this->api->parseItemsFromProduct($products),
                    bins: $this->api->parseBinsFromPackaging($suitablePackaging),
                )
            )->bins;

            if (!$bins) {
                throw PackagingNotFound::make('No packaging found');
            }

            usort($bins, fn($a, $b) => $a->usedVolume <=> $b->usedVolume);

            /** @var Packaging $packaging */
            $packaging = null;

            foreach ($suitablePackaging as $p) {
                if ($p->getId() === $bins[0]->id) {
                    $packaging = $p;
                    break;
                }
            }

            return $packaging;
        } catch (ApiErrorException) {
            // report error, log "using default fallback"
            // assume api is down, try to find a smallest suitable packaging from database
            usort($suitablePackaging, fn($a, $b) => $a->getVolume() <=> $b->getVolume());

            return $suitablePackaging[0];
        }
    }

    /**
     * @inheritDoc
     */
    public function getCachedPackaging(string $hash): Packaging
    {
        $now = new \DateTimeImmutable();

        $query = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(CachedPackaging::class, 'c')
            ->where('c.inputHash = :input_hash')
            ->andWhere('c.expiredAt > :now')
            ->setParameter('input_hash', $hash)
            ->setParameter('now', $now)
            ->getQuery();

        /** @var ?CachedPackaging $cached */
        $cached = $query->getOneOrNullResult();
        if (!$cached?->getPackaging()) {
            throw PackagingNotFound::make('No packaging found');
        }

        return $cached->getPackaging();
    }

    /**
     * @inheritDoc
     */
    public function saveCachedPackaging($hash, Packaging $packaging): void
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('c')
            ->from(CachedPackaging::class, 'c')
            ->where('c.inputHash = :input_hash')
            ->setParameter('input_hash', $hash)
            ->getQuery();

        /** @var ?CachedPackaging $cached */
        $cached = $query->getOneOrNullResult();
        if (!$cached) {
            $cached = new CachedPackaging();
            $cached->setInputHash($hash);
        }

        $cached->setPackaging($packaging);
        $cached->setExpiredAt(new \DateTime('+1 day'));
        $this->entityManager->persist($cached);
        $this->entityManager->flush();
    }

    /**
     * Obtain the dimensions of the largest product.
     *
     * @param Product[] $products
     * @return Dimensions
     */
    protected function getDimensions(array $products): Dimensions
    {
        $dimensions = new Dimensions();

        foreach ($products as $product) {
            $dimensions->volume = max(
                $dimensions->volume,
                $product->width * $product->height * $product->length
            );
            $dimensions->weight = max($dimensions->weight, $product->weight);
        }

        return $dimensions;
    }

    /**
     * Obtain the smallest packaging that can fit the given dimensions.
     *
     * @param Dimensions $dimensions
     * @return Packaging[]
     */
    protected function getSuitablePackagingByDimensions(Dimensions $dimensions): array
    {
        $query = $this->entityManager->createQueryBuilder()
            ->select('p')
            ->from(Packaging::class, 'p')
            ->where('(p.width * p.height * p.length) >= :volume')
            ->andWhere('p.maxWeight >= :weight')
            ->setParameter('volume', $dimensions->volume)
            ->setParameter('weight', $dimensions->weight);

        return $query->getQuery()->getResult();
    }
}
