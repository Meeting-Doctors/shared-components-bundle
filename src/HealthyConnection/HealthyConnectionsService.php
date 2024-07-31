<?php

namespace SharedBundle\HealthyConnection;

class HealthyConnectionsService
{
    /**
     * @var HealthyConnectionInterface[]
     */
    private array $healthyConnections;

    public function __construct(HealthyConnectionInterface ...$healthyConnections)
    {
        $this->healthyConnections = $healthyConnections;
    }

    public function getHealthyConnections(): array
    {
        $result = [];

        foreach ($this->healthyConnections as $healthyConnection) {
            $result[$healthyConnection->name()] = $healthyConnection->isHealthy();
        }

        return $result;
    }
}