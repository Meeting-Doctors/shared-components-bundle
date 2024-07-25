<?php

declare(strict_types=1);

namespace SharedBundle\EventHandling;

use Shared\Domain\DomainEvent;
use Shared\Domain\DomainEventStream;
use Shared\EventHandling\EventBusInterface;
use Shared\EventHandling\EventListenerInterface;
use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

final class AsyncEventPublisher implements EventSubscriberInterface, EventListenerInterface
{
    /** @var DomainEvent[] */
    private array $events = [];

    public function __construct(
        private readonly EventBusInterface $bus
    ) {
    }

    #[\Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::TERMINATE => 'publish',
            ConsoleEvents::TERMINATE => 'publish',
        ];
    }

    /**
     * @throws \Throwable
     */
    public function publish(): void
    {
        if ([] === $this->events) {
            return;
        }

        $this->bus->publish(new DomainEventStream(...$this->events));
    }

    #[\Override]
    public function handle(DomainEvent $event): void
    {
        $this->events[] = $event;
    }
}
