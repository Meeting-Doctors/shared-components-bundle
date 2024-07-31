<?php

declare(strict_types=1);

namespace SharedBundle\MongoDB;

use MongoDB\Client;
use SharedBundle\HealthyConnection\HealthyConnectionInterface;

class MongoDBHealthyConnection implements HealthyConnectionInterface
{
    private const string NAME = 'mongodb';

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

    public function name(): string
    {
        return self::NAME;
    }
}
