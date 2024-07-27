<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Exception\Strategy;

use Shared\Exception\NotFoundException;
use SharedBundle\UI\Http\Rest\Response\OpenApi;

class NotFoundExceptionToHttpStatusCodeStrategy implements ExceptionToHttpStatusCodeStrategyInterface
{
    public function getException(): string
    {
        return NotFoundException::class;
    }

    public function getStatusCode(): int
    {
        return OpenApi::HTTP_NOT_FOUND;
    }
}
