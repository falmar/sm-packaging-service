<?php

use App\Application;
use GuzzleHttp\Psr7\Message;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Uri;

/** @var \Psr\Container\ContainerInterface $container */
$container = require __DIR__ . '/src/bootstrap.php';

/** @var \Doctrine\ORM\EntityManagerInterface $entityManager */
$entityManager = $container->get(\Doctrine\ORM\EntityManagerInterface::class);
/** @var \App\Domains\BinPack\BinPackApiInterface $binPacker */
$binPacker = $container->get(\App\Domains\BinPack\BinPackApiInterface::class);

$request = new Request('POST', new Uri('http://localhost/pack'), ['Content-Type' => 'application/json'], $argv[1]);

$application = new Application(
    entityManager: $entityManager,
    binPacker: $binPacker
);
$response = $application->run($request);

echo "<<< In:\n" . Message::toString($request) . "\n\n";
echo ">>> Out:\n" . Message::toString($response) . "\n\n";
