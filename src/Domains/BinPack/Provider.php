<?php

namespace App\Domains\BinPack;

use DI\Container;
use Doctrine\ORM\EntityManagerInterface;

class Provider
{
    public function register(Container $container): void
    {
        $container->set(BinPackApiInterface::class, function (Container $container) {
            // should create a "settings provider" instead of raw $_ENV access
            return new BinPackApiApi(
                username: $_ENV['BINPACK_USERNAME'],
                apiKey: $_ENV['BINPACK_API_KEY']
            );
        });

        $container->set(BinPackServiceInterface::class, function (Container $container) {
            return new BinPackService(
                api: $container->get(BinPackApiInterface::class),
                entityManager: $container->get(EntityManagerInterface::class)
            );
        });
    }
}
