<?php

namespace SharedBundle\HealthyConnection;

interface HealthyConnectionInterface
{
    public function name(): string;

    public function isHealthy(): bool;
}