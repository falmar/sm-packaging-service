<?php

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\ORMSetup;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..', '.env');
$dotenv->load();

$container = new DI\Container();

$container->set(\Doctrine\ORM\EntityManagerInterface::class, function () {
    $config = ORMSetup::createAttributeMetadataConfiguration([__DIR__], true);
    $config->setNamingStrategy(new UnderscoreNamingStrategy());

    return EntityManager::create([
        'driver' => 'pdo_mysql',
        'host' => 'shipmonk-packing-mysql',
        'user' => 'root',
        'password' => 'secret',
        'dbname' => 'packing',
    ], $config);
});

// Bind the BinPackInterface to the BinPack class
(new \App\Domains\BinPack\Provider())->register($container);

return $container;
