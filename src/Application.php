<?php

namespace App;

use App\Domains\BinPack\BinPackServiceInterface;
use App\Domains\BinPack\Entities\Packaging;
use App\Domains\BinPack\Exceptions\CommonException;
use App\Domains\BinPack\Exceptions\PackagingNotFound;
use App\Domains\BinPack\ValueObjects\Product;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class Application
{
    private BinPackServiceInterface $service;

    public function __construct(
        BinPackServiceInterface $service
    ) {
        $this->service = $service;
    }

    /**
     * @param RequestInterface $request
     * @return ResponseInterface
     * @throws \JsonException
     */
    public function run(RequestInterface $request): ResponseInterface
    {
        $content = $request->getBody()->getContents();
        $hash = hash('sha256', $content);

        try {
            $package = $this->service->getCachedPackaging($hash);

            $response = (new Response())
                ->withStatus(200)
                ->withHeader('Content-Type', 'application/json');
            $response->getBody()->write(
                $this->jsonEncode($package)
            );

            // package found we can return early
            return $response;
        } catch (PackagingNotFound $e) {
            $package = null;
        }

        $body = $this->jsonDecode($content);

        $products = [];
        foreach ($body['products'] as $p) {
            $products[] = new Product(
                id: $p['id'],
                width: $p['width'],
                height: $p['height'],
                length: $p['length'],
                weight: $p['weight'],
            );
        }

        $httpStatus = 200;
        $body = null;

        try {
            $package = $this->service->getSmallestBoxForProducts(
                $products
            );

            $body = $this->jsonEncode($package);

            // save the package to cache
            // should send this to a queue
            $this->service->saveCachedPackaging($hash, $package);
        } catch (PackagingNotFound $e) {
            $httpStatus = 404;
            $body = $this->jsonEncodeError($e);
        } catch (\Exception $exception) {
            $httpStatus = 500;

            if ($exception instanceof CommonException) {
                $body = $this->jsonEncodeError($exception);
            } else {
                $body = $this->jsonEncodeError(CommonException::make($exception->getMessage()));
            }
        }

        $response = (new Response())
            ->withStatus($httpStatus)
            ->withHeader('Content-Type', 'application/json');
        $response->getBody()->write($body);

        return $response;
    }

    /**
     * @param string $content
     * @return array<string, mixed>
     * @throws \JsonException
     */
    protected function jsonDecode(string $content): array
    {
        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * @param Packaging $packaging
     * @return string
     * @throws \JsonException
     */
    protected function jsonEncode(Packaging $packaging): string
    {
        return json_encode($packaging, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }

    /**
     * @param CommonException $e
     * @return string
     * @throws \JsonException
     */
    protected function jsonEncodeError(CommonException $e): string
    {
        return json_encode([
            'code' => $e->errorCode,
            'message' => $e->errorMessage,
        ], JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT);
    }
}
