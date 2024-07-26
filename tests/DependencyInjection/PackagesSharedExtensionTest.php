<?php

declare(strict_types=1);

namespace SharedBundle\Tests\DependencyInjection;

use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Shared\CommandHandling\CommandBusInterface;
use Shared\CommandHandling\QueryBusInterface;
use Shared\EventHandling\EventBusInterface;
use Shared\EventSourcing\EventStreamDecoratorInterface;
use Shared\EventStore\EventStoreInterface;
use Shared\EventStore\EventStoreManagerInterface;
use SharedBundle\DependencyInjection\SharedExtension;
use SharedBundle\EventStore\DBALEventStore;

final class PackagesSharedExtensionTest extends AbstractExtensionTestCase
{
    #[\Override]
    protected function getContainerExtensions(): array
    {
        return [
            new SharedExtension(),
        ];
    }

    public function test_must_contains_services(): void
    {
        $this->load();

        self::assertContainerBuilderHasService(CommandBusInterface::class);
        self::assertContainerBuilderHasService(QueryBusInterface::class);
        self::assertContainerBuilderHasService(EventBusInterface::class);
        self::assertContainerBuilderHasService(EventStreamDecoratorInterface::class);
        self::assertContainerBuilderHasService(DBALEventStore::class);
        self::assertContainerBuilderHasService(EventStoreInterface::class);
        self::assertContainerBuilderHasService(EventStoreManagerInterface::class);
    }
}
