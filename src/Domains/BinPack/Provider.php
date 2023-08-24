<?php

namespace App\Domains\BinPack;

use DI\Container;

class Provider
{
    public function register(Container $container)
    {
        $container->set(BinPackApiInterface::class, function (Container $container) {
            // should create a "settings provider" instead of raw $_ENV access
            return new BinPackApiApi(
                username: $_ENV['BINPACK_USERNAME'],
                apiKey: $_ENV['BINPACK_API_KEY']
            );
        });
    }
}
