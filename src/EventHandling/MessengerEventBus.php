<?php

declare(strict_types=1);

namespace SharedBundle\EventHandling;

use Shared\Domain\DomainEventStream;
use Shared\EventHandling\EventBusInterface;
use SharedBundle\Exception\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerEventBus implements EventBusInterface
{
    use MessageBusExceptionTrait;

    public function __construct(
        private MessageBusInterface $eventBus
    ) {
    }

    public function publish(DomainEventStream $stream): void
    {
        foreach ($stream->events as $event) {
            $this->eventBus->dispatch($event, [
                new AmqpStamp(strtr($event::class, '\\', '.'))
            ]);
        }
    }
}
