<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Exceptions\PackagingNotFound;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\BinPack\ValueObjects\API\Bin;
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
    public function getSmallestBoxForProducts(array $products): Packaging
    {
        if (!$products) {
            throw new \InvalidArgumentException('No products found');
        }

        $dimensions = $this->getDimensions($products);
        $suitablePackaging = $this->getSuitablePackagingByDimensions($dimensions);

        if (!$suitablePackaging) {
            throw new PackagingNotFound();
        }

        try {
            $bins = $this->api->packShipment(
                new PackShipmentInput(
                    items: $this->api->parseItemsFromProduct($products),
                    bins: $this->api->parseBinsFromPackaging($suitablePackaging),
                )
            )->bins;

            usort($bins, fn($a, $b) => $a->usedVolume <=> $b->usedVolume);

            /** @var ?Packaging $packaging */
            $packaging = null;

            foreach ($bins as $bin) {
                foreach ($suitablePackaging as $p) {
                    if ($p->getId() === $bin->id) {
                        $packaging = $p;
                        break;
                    }
                }
            }

            if (!$packaging) {
                throw new PackagingNotFound('No suitable packaging found');
            }

            return $packaging;
        } catch (ApiErrorException $apiErrorException) {
            // assume api is down, try to find a smallest suitable packaging from database
            usort($suitablePackaging, fn($a, $b) => $a->getVolume() <=> $b->getVolume());

            return $suitablePackaging[0];
        }
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
     * Obtain the smallest box that can fit the given dimensions.
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
