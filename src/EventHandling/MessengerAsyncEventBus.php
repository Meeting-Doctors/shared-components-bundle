<?php

declare(strict_types=1);

namespace SharedBundle\EventHandling;

use Shared\Domain\DomainEvent;
use SharedBundle\Exception\MessageBusExceptionTrait;
use Symfony\Component\Messenger\Bridge\Amqp\Transport\AmqpStamp;
use Symfony\Component\Messenger\Exception\HandlerFailedException;
use Symfony\Component\Messenger\MessageBusInterface;

final readonly class MessengerAsyncEventBus
{
    use MessageBusExceptionTrait;

    public function __construct(
        private MessageBusInterface $messageBus
    ) {
    }

    /**
     * @throws \Throwable
     */
    public function handle(DomainEvent $event): void
    {
        try {
            $this->messageBus->dispatch($event, [
                new AmqpStamp($event::class),
            ]);
        } catch (HandlerFailedException $handlerFailedException) {
            $this->throwException($handlerFailedException);
        }
    }
}
