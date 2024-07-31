<?php

declare(strict_types=1);

namespace SharedBundle\DBAL;

use Doctrine\DBAL\Connection;
use SharedBundle\HealthyConnection\HealthyConnectionInterface;

final readonly class DBALHealthyConnection implements HealthyConnectionInterface
{
    private const string NAME = 'mysql';

    public function __construct(
        private Connection $connection
    ) {
    }

    public function isHealthy(): bool
    {
        try {
            $dummySelectSQL = $this->connection->getDatabasePlatform()->getDummySelectSQL();

            $this->connection->executeQuery($dummySelectSQL);

            return true;
        } catch (\Throwable) {
            $this->connection->close();

            return false;
        }
    }

    public function name(): string
    {
        return self::NAME;
    }
}
