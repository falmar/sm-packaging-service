<?php

namespace App;

use App\Domains\BinPack\BinPackApiInterface;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\ValueObjects\Bin;
use App\Domains\ValueObjects\Item;
use App\Entity\Packaging;
use Doctrine\ORM\EntityManagerInterface;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{
    private EntityManagerInterface $entityManager;
    private BinPackApiInterface $binPacker;

    public function __construct(
        EntityManagerInterface $entityManager,
        BinPackApiInterface $binPacker
    ) {
        $this->entityManager = $entityManager;
        $this->binPacker = $binPacker;
    }

    public function run(RequestInterface $request): ResponseInterface
    {
        $body = json_decode($request->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

        [$items, $dimensions] = $this->parseProducts($body['products']);
        $bins = $this->getMatchingBins($dimensions);

        $spec = new PackShipmentInput();
        $spec->products = $items;
        $spec->boxes = $bins;

        $out = $this->binPacker->packShipment($spec);

        $response = new Response();

        $response->getBody()->write(json_encode(['boxes' => $out->bins], JSON_THROW_ON_ERROR));

        return $response;
    }

    /**
     * @param array $products
     * @return array
     */
    protected function parseProducts(array $products): array
    {
        $results = [];
        $maxDimensions = [
            'width' => 0,
            'height' => 0,
            'length' => 0,
            'weight' => 0,
        ];

        foreach ($products as $product) {
            $results[] = new Item(
                id: $product['id'],
                width: $product['width'],
                height: $product['height'],
                length: $product['length'],
                weight: $product['weight']
            );

            if ($product['width'] > $maxDimensions['width']) {
                $maxDimensions['width'] = $product['width'];
            }
            if ($product['height'] > $maxDimensions['height']) {
                $maxDimensions['height'] = $product['height'];
            }
            if ($product['length'] > $maxDimensions['length']) {
                $maxDimensions['length'] = $product['length'];
            }
            if ($product['weight'] > $maxDimensions['weight']) {
                $maxDimensions['weight'] = $product['weight'];
            }
        }

        return [$results, $maxDimensions];
    }

    /**
     * @param array $dimensions
     * @return array
     */
    protected function getMatchingBins(array $dimensions): array
    {
        $query = $this->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from(Packaging::class, 'p')
            ->where('p.width >= :width')
            ->andWhere('p.height >= :height')
            ->andWhere('p.length >= :length')
            ->andWhere('p.maxWeight >= :weight')
            ->setParameter('width', $dimensions['width'])
            ->setParameter('height', $dimensions['height'])
            ->setParameter('length', $dimensions['length'])
            ->setParameter('weight', $dimensions['weight'])
            ->getQuery();

        $result = $query->getResult();
        $mapped = [];

        /** @var Packaging $packaging */
        foreach ($result as $packaging) {
            $mapped[] = new Bin(
                id: $packaging->getId(),
                width: $packaging->getWidth(),
                height: $packaging->getHeight(),
                length: $packaging->getLength(),
                maxWeight: $packaging->getMaxWeight(),
                weight: 0,
            );
        }

        return $mapped;
    }
}
