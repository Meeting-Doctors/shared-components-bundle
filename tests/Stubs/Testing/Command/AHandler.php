<?php

declare(strict_types=1);

namespace SharedBundle\Tests\Stubs\Testing\Command;

use Shared\CommandHandling\CommandHandlerInterface;
use Shared\Domain\DomainEventStream;
use Shared\Domain\Uuid;
use Shared\EventHandling\EventBusInterface;
use SharedBundle\Tests\Stubs\DomainEventStub;
use SharedBundle\Tests\Stubs\DomainMessageStubPayload;

final readonly class AHandler implements CommandHandlerInterface
{
    public function __construct(
        private EventBusInterface $eventBus
    ) {
    }

    public function __invoke(ACommand $command): void
    {
        $this->eventBus->publish(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));
    }
}
