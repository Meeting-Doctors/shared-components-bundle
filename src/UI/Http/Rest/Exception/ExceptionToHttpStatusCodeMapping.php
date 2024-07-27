<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Exception;

use SharedBundle\UI\Http\Rest\Exception\Strategy\ExceptionToHttpStatusCodeStrategyInterface;
use SharedBundle\UI\Http\Rest\Response\OpenApi;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

final class ExceptionToHttpStatusCodeMapping
{
    private array $mapping = [];

    public function __construct(ExceptionToHttpStatusCodeStrategyInterface ...$strategies)
    {
        foreach ($strategies as $strategy) {
            $this->mapping[$strategy->getException()] = $strategy->getStatusCode();
        }
    }

    public function handle(\Throwable $exception): int
    {
        if ($exception instanceof HttpExceptionInterface) {
            return $exception->getStatusCode();
        }

        foreach ($this->mapping as $class => $status) {
            if (is_a($exception::class, $class, true)) {
                return $status;
            }
        }

        return OpenApi::HTTP_INTERNAL_SERVER_ERROR;
    }
}
