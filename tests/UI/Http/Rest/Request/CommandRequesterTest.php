<?php

declare(strict_types=1);

namespace SharedBundle\Tests\UI\Http\Rest\Request;

use PHPUnit\Framework\TestCase;
use SharedBundle\UI\Http\Rest\Request\CommandRequester;
use SharedBundle\UI\Http\Rest\Request\Input;
use SharedBundle\UI\Http\Rest\Request\RequestInterface;

final class CommandRequesterTest extends TestCase
{
    public function test_must_handle_command(): void
    {
        $context = new CommandRequester(
            new ARequest(static fn () => self::assertTrue(true))
        );

        $context->request(Input::empty());
    }
}

final readonly class ARequest implements RequestInterface
{
    public function __construct(
        private \Closure $callable
    ) {
    }

    #[\Override]
    public function support(Input $input): bool
    {
        return true;
    }

    #[\Override]
    public function doWithRequest(Input $input): void
    {
        call_user_func($this->callable);
    }
}
