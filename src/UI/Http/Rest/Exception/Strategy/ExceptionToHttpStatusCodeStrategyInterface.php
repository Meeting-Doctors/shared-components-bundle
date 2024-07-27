<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Exception\Strategy;

interface ExceptionToHttpStatusCodeStrategyInterface
{
    public function getException(): string;

    public function getStatusCode(): int;
}
