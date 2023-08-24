<?php

use App\Application;
use App\Domains\BinPack\BinPackServiceInterface;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/src/bootstrap.php';

/** @var BinPackServiceInterface $binPacker */
$binPacker = $container->get(BinPackServiceInterface::class);

$request = new Request('POST', new Uri('http://localhost/pack'), ['Content-Type' => 'application/json'], $argv[1]);

$application = new Application(
    service: $binPacker
);
$response = $application->run($request);

echo "<<< In:\n" . Message::toString($request) . "\n\n";
echo ">>> Out:\n" . Message::toString($response) . "\n\n";
