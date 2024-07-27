<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Exception\Strategy;

use Shared\EventSourcing\AggregateRootAlreadyExistsException;
use SharedBundle\UI\Http\Rest\Response\OpenApi;

class AlreadyExistsExceptionToHttpStatusCodeStrategy implements ExceptionToHttpStatusCodeStrategyInterface
{
    public function getException(): string
    {
        return AggregateRootAlreadyExistsException::class;
    }

    public function getStatusCode(): int
    {
        return OpenApi::HTTP_CONFLICT;
    }
}
