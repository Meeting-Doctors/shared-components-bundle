<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Exception\Strategy;

use InvalidArgumentException;
use SharedBundle\UI\Http\Rest\Response\OpenApi;

class InvalidArgumentExceptionToHttpStatusCodeStrategy implements ExceptionToHttpStatusCodeStrategyInterface
{
    public function getException(): string
    {
        return InvalidArgumentException::class;
    }

    public function getStatusCode(): int
    {
        return OpenApi::HTTP_BAD_REQUEST;
    }
}
