<?php

declare(strict_types=1);

namespace SharedBundle\Tests\EventStore;

use Shared\Domain\DomainEvent;
use Shared\Domain\DomainEventStream;
use Shared\Domain\Uuid;
use Shared\EventStore\CallableEventVisitor;
use Shared\EventStore\DomainEventStreamNotFoundException;
use Shared\EventStore\EventStoreInterface;
use Shared\EventStore\EventStoreManagerInterface;
use Shared\EventStore\PlayheadAlreadyExistsException;
use SharedBundle\EventStore\DBALEventStore;
use SharedBundle\Tests\Stubs\DomainEventStub;
use SharedBundle\Tests\Stubs\DomainMessageStubPayload;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

final class DBALEventStoreTest extends KernelTestCase
{
    protected EventStoreInterface&EventStoreManagerInterface $eventStore;

    /**
     * @throws \Symfony\Component\Console\Exception\ExceptionInterface
     */
    public function createDatabaseSchema(): void
    {
        $kernel = self::bootKernel();
        $application = new Application($kernel);
        $application->get('dbal:run-sql')
            ->run(
                new ArrayInput([
                    'sql' => DBALEventStore::TABLE_SCHEMA,
                ]),
                new NullOutput()
            );
    }

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->createDatabaseSchema();

        /** @var DBALEventStore $eventStore */
        $eventStore = self::getContainer()->get(DBALEventStore::class);
        $this->eventStore = $eventStore;
    }

    #[\Override]
    protected function tearDown(): void
    {
        $connection = self::$kernel->getContainer()->get('doctrine.dbal.default_connection');
        $platform = $connection->getDatabasePlatform();
        $truncateSql = $platform->getTruncateTableSQL(DBALEventStore::TABLE_NAME);

        $connection->executeStatement($truncateSql);

        parent::tearDown();
    }

    public function test_must_throw_stream_not_found_exception_when_load_stream_from_id(): void
    {
        self::expectException(DomainEventStreamNotFoundException::class);

        $this->eventStore->load(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'));
    }

    public function test_must_throw_stream_not_found_exception_when_load_stream_from_playhead(): void
    {
        self::expectException(DomainEventStreamNotFoundException::class);

        $this->eventStore->load(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'), 0);
    }

    public function test_must_throw_playhead_already_exists_exception_when_append_stream(): void
    {
        self::expectException(PlayheadAlreadyExistsException::class);

        $this->eventStore->append(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));

        $this->eventStore->append(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));
        // var_dump($this->eventStore->load(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac')));die;
    }

    public function test_must_load_stream_from_id(): void
    {
        $this->eventStore->append(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));

        $stream = $this->eventStore->load(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'));

        self::assertInstanceOf(DomainEventStream::class, $stream);
    }

    public function test_must_load_stream_from_playhead(): void
    {
        $this->eventStore->append(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));

        $stream = $this->eventStore->load(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'), 0);

        self::assertInstanceOf(DomainEventStream::class, $stream);
    }

    public function test_must_visit_events(): void
    {
        $this->eventStore->append(new DomainEventStream(DomainEventStub::occur(
            new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'),
            new DomainMessageStubPayload()
        )));

        $eventVisitor = new CallableEventVisitor(
            static fn (DomainEvent $message) => self::assertSame('9db0db88-3e44-4d2b-b46f-9ca547de06ac', $message->aggregateId()->uuid)
        );

        $this->eventStore->visitEvents(new Uuid('9db0db88-3e44-4d2b-b46f-9ca547de06ac'), $eventVisitor);
    }
}
