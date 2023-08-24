<?php

namespace App\Domains\BinPack;

use App\Domains\BinPack\Exceptions\ApiErrorException;
use App\Domains\BinPack\Specs\PackShipmentInput;
use App\Domains\BinPack\Specs\PackShipmentOutput;
use App\Domains\ValueObjects\Bin;
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
                'json' => [
                    'username' => $this->username,
                    'api_key' => $this->apiKey,
                    'items' => $input->products,
                    'bins' => $input->boxes,
                ]
            ]);

            $output = new PackShipmentOutput();

            $body = json_decode($response->getBody()->getContents(), true, 512, JSON_THROW_ON_ERROR);

            /** @var array{} $bin */
            foreach ($body['response']['bins_packed'] ?? [] as $packed) {
                $bin = $packed['bin_data'];

                $output->bins[] = new Bin(
                    id: $bin['id'],
                    width: $bin['w'],
                    height: $bin['h'],
                    length: $bin['d'],
                    maxWeight: $bin['used_weight'],
                    weight: $bin['weight'],
                );
            }

            return $output;
        } catch (ClientExceptionInterface $exception) {
            throw new ApiErrorException();
        }
    }
}
