<?php

declare(strict_types=1);

namespace SharedBundle\MongoDB;

use MongoDB\Client;

class MongoDBHealthyConnection
{
    public function __construct(private readonly Client $client)
    {
    }

    public function isHealthy(): bool
    {
        try {
            $this->client->listDatabases();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
