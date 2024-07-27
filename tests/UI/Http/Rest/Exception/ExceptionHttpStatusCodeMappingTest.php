<?php

declare(strict_types=1);

namespace SharedBundle\Tests\UI\Http\Rest\Exception;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SharedBundle\UI\Http\Rest\Exception\ExceptionToHttpStatusCodeMapping;
use SharedBundle\UI\Http\Rest\Exception\Strategy\InvalidArgumentExceptionToHttpStatusCodeStrategy;
use SharedBundle\UI\Http\Rest\Response\OpenApi;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

final class ExceptionHttpStatusCodeMappingTest extends TestCase
{
    public function test_must_register_an_exception(): void
    {
        $expected = 400;

        $mapping = new ExceptionToHttpStatusCodeMapping(
            new InvalidArgumentExceptionToHttpStatusCodeStrategy()
        );

        $statusCode = $mapping->handle(new InvalidArgumentException());

        self::assertSame($expected, $statusCode);
    }

    public function test_must_handle_unregistered_exception(): void
    {
        $mapping = new ExceptionToHttpStatusCodeMapping();

        $statusCode = $mapping->handle(new \RuntimeException());

        self::assertSame(OpenApi::HTTP_INTERNAL_SERVER_ERROR, $statusCode);
    }

    public function test_must_handle_http_exception(): void
    {
        $mapping = new ExceptionToHttpStatusCodeMapping();

        $statusCode = $mapping->handle(new ConflictHttpException());

        self::assertSame(OpenApi::HTTP_CONFLICT, $statusCode);
    }
}
