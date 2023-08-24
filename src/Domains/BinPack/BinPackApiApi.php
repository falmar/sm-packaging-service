<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\BinPack\Specs\PackShipmentOutput;
use App\Domains\BinPack\ValueObjects\API\Bin;
use App\Domains\BinPack\ValueObjects\API\Item;
use App\Domains\BinPack\ValueObjects\Product;
use GuzzleHttp\Client;
use Psr\Http\Client\ClientExceptionInterface;

class BinPackApiApi implements BinPackApiInterface
{
    private Client $client;

    private string $apiKey;
    private string $username;

    public function __construct(string $username, string $apiKey)
    {
        $this->username = $username;
        $this->apiKey = $apiKey;

        $this->client = new Client([
            'base_uri' => 'https://eu.api.3dbinpacking.com',
            'timeout' => 2.0,
        ]);
    }

    public function packShipment(PackShipmentInput $input): PackShipmentOutput
    {
        try {
            $response = $this->client->post('/packer/packIntoMany', [
                'json' => json_encode([
                    'username' => $this->username,
                    'api_key' => $this->apiKey,
                    'items' => $input->items,
                    'bins' => $input->bins,
                ]),
            ]);

            $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            if ($error = $this->getApiError($body['response']['errors'] ?? [])) {
                throw ApiErrorException::make($error);
            }

            $output = new PackShipmentOutput();
            $output->bins = $this->parseBinsFromApi($body['response']['bins_packed'] ?? []);

            return $output;
        } catch (ClientExceptionInterface $exception) {
            throw ApiErrorException::make($exception->getMessage());
        }
    }

    /**
     * @param Product[] $products
     * @return Item[]
     */
    public function parseItemsFromProduct(array $products): array
    {
        $results = [];

        foreach ($products as $product) {
            $results[] = new Item(
                id: $product->id,
                width: $product->width,
                height: $product->height,
                length: $product->length,
                weight: $product->weight,
            );
        }

        return $results;
    }

    /**
     * @param Packaging[] $packages
     * @return Bin[]
     */
    public function parseBinsFromPackaging(array $packages): array
    {
        $results = [];

        foreach ($packages as $package) {
            $results[] = new Bin(
                id: $package->getId(),
                width: $package->getWidth(),
                height: $package->getHeight(),
                length: $package->getLength(),
                maxWeight: $package->getMaxWeight(),
            );
        }

        return $results;
    }

    /**
     * @param array<string, mixed> $packed
     * @return Bin[]
     */
    protected function parseBinsFromApi(array $packed): array
    {
        $results = [];

        foreach ($packed as $b) {
            /** @var array<string, mixed> $b */

            $bin = $b['bin_data'];
            $results[] = new Bin(
                id: $bin['id'],
                width: $bin['w'],
                height: $bin['h'],
                length: $bin['d'],
                maxWeight: $bin['used_weight'],
                weight: $bin['weight'],
            );
        }

        return $results;
    }

    /**
     * Jump at first encounter of critical or notice error.
     *
     * @param array<string, mixed> $errors
     * @return string
     */
    protected function getApiError(array $errors): string
    {
        foreach ($errors as $error) {
            if ($error['level'] == 'critical' || $error['level'] == 'notice') {
                return $error['message'];
            }
        }

        return '';
    }
}
