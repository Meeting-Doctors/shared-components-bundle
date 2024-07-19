<?php

declare(strict_types=1);

namespace SharedBundle\Tests\MongoDB;

use Exception;
use MongoDB\Client;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use SharedBundle\MongoDB\MongoDBHealthyConnection;

class MongoDBHealthyConnectionTest extends TestCase
{
    private MongoDBHealthyConnection $healthyConnection;
    private Client&MockObject $client;

    protected function setUp(): void
    {
        $this->client = $this->createMock(Client::class);
        $this->healthyConnection = new MongoDBHealthyConnection($this->client);
    }

    public function test_return_true_when_list_database(): void
    {
        $this->client
            ->expects($this->once())
            ->method('listDatabases')
            ->willReturn([]);

        $result = $this->healthyConnection->isHealthy();

        $this->assertEquals(true, $result);
    }

    public function test_return_false_when_throw_exception(): void
    {
        $this->client
            ->expects($this->once())
            ->method('listDatabases')
            ->willThrowException(new Exception('Exception listDatabases'));

        $result = $this->healthyConnection->isHealthy();

        $this->assertEquals(false, $result);
    }
}
