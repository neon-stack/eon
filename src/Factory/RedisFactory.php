<?php

declare(strict_types=1);

namespace App\Factory;

use Predis\Client;

class RedisFactory
{
    public function __construct(
        private string $redisAuth,
    ) {
    }

    public function createRedis(): Client
    {
        return new Client($this->redisAuth);
    }
}
