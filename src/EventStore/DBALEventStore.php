<?php

declare(strict_types=1);

namespace SharedBundle\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\DBAL\Types\Types;
use Shared\Criteria;
use Shared\Domain\DateTimeImmutable;
use Shared\Domain\DomainEvent;
use Shared\Domain\DomainEventStream;
use Shared\Domain\PlayHead;
use Shared\Domain\Uuid;
use Shared\EventStore\DomainEventStreamNotFoundException;
use Shared\EventStore\EventStoreException;
use Shared\EventStore\EventStoreInterface;
use Shared\EventStore\EventStoreManagerInterface;
use Shared\EventStore\EventVisitorInterface;
use Shared\EventStore\PlayheadAlreadyExistsException;
use Shared\Serializer\Serializer;
use SharedBundle\Criteria\CriteriaConverterException;
use SharedBundle\Persistence\Doctrine\DoctrineCriteriaConverter;

final readonly class DBALEventStore implements EventStoreInterface, EventStoreManagerInterface
{
    public const string TABLE_NAME = 'domain_events';
    public const string TABLE_SCHEMA = 'CREATE TABLE IF NOT EXISTS `' . self::TABLE_NAME . '` (
        `id` VARCHAR(36) NOT NULL COLLATE utf8mb4_general_ci,
        `aggregate_id` VARCHAR(36) NOT NULL COLLATE utf8mb4_general_ci,
        `playhead` INT(10) UNSIGNED NOT NULL,
        `event_class` VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci,
        `payload` LONGTEXT NOT NULL COLLATE utf8mb4_general_ci,
        `metadata` LONGTEXT NOT NULL COLLATE utf8mb4_general_ci,
        `recorded_at` DATETIME(6) NOT NULL,
        PRIMARY KEY (`id`) USING BTREE,
        UNIQUE INDEX `event_aggregate_id_playhead_idx` (`aggregate_id`, `playhead`) USING BTREE,
        INDEX `event_recorded_at_idx` (`recorded_at`) USING BTREE,
        INDEX `event_playhead_idx` (`playhead`) USING BTREE
    )
    COLLATE=utf8mb4_general_ci
    ENGINE=InnoDB;
';
    private const string SQL_GET = 'SELECT * 
        FROM `' . self::TABLE_NAME . '`
        WHERE (`aggregate_id` = :aggregate_id)
        ORDER BY `playhead` ASC
';
    const SQL_GET_WITH_PLAYHEAD = 'SELECT * 
        FROM `' . self::TABLE_NAME . '`
        WHERE (`aggregate_id` = :aggregate_id)
        AND (`playhead` >= :playhead)
        ORDER BY `playhead` ASC
';

    public function __construct(private readonly Connection $connection)
    {
    }

    /**
     * @throws DomainEventStreamNotFoundException
     * @throws CriteriaConverterException
     */
    #[\Override]
    public function load(Uuid $id, ?int $playhead = null): DomainEventStream
    {
        if (null !== $playhead) {
            return $this->loadFromPlayhead($id, $playhead);
        }

        $results = $this->connection->fetchAllAssociative(
            self::SQL_GET,
            [
                'aggregate_id' => $id->uuid,
            ],
            [
                'aggregate_id' => Types::STRING,
            ]
        );

        if ([] === $results) {
            throw DomainEventStreamNotFoundException::new($id);
        }

        $events = array_map(function (array $data) {
            return $this->rowToDomainEvent($data);
        }, $results);

        return new DomainEventStream(...$events);
    }

    #[\Override]
    public function append(DomainEventStream $stream): void
    {
        foreach ($stream->events as $event) {
            try {
                $this->connection->insert(
                    self::TABLE_NAME,
                    [
                        'id' => $event->id()->uuid,
                        'aggregate_id' => $event->aggregateId()->uuid,
                        'event_class' => $event::class,
                        'payload' => json_encode(Serializer::serialize($event->payload()), JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION),
                        'playhead' => $event->playHead()->value,
                        'metadata' => json_encode(Serializer::serialize($event->metadata()), JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION),
                        'recorded_at' => $event->recordedAt()->dateTime
                    ],
                );
            } catch (ConstraintViolationException) {
                throw PlayheadAlreadyExistsException::new($event->id(), $event->playHead()->value);
            } catch (DBALException $e) {
                throw EventStoreException::new($e);
            }
        }
    }

    /**
     * @throws CriteriaConverterException
     */
    #[\Override]
    public function visitEvents(Uuid $aggregateId, EventVisitorInterface $eventVisitor, ?int $playhead = null): void
    {
        $results = $this->connection->fetchAllAssociative(
            self::SQL_GET,
            [
                'aggregate_id' => $aggregateId->uuid,
            ],
            [
                'aggregate_id' => Types::STRING,
            ]
        );
        /*
        $events = $this->search(
            $criteria,
            new Criteria\OrderX(new Criteria\ByPlayhead(Criteria\Expr\Order::ASC))
        );
        */

        foreach ($results as $data) {
            $event = $this->rowToDomainEvent($data);

            $eventVisitor->doWithEvent($event);
        }
    }

    private function loadFromPlayhead(Uuid $id, int $playhead): DomainEventStream
    {
        $results = $this->connection->fetchAllAssociative(
            self::SQL_GET_WITH_PLAYHEAD,
            [
                'aggregate_id' => $id->uuid,
                'playhead' => $playhead,
            ],
            [
                'aggregate_id' => Types::STRING,
                'playhead' => Types::INTEGER,
            ]
        );

        if ([] === $results) {
            throw DomainEventStreamNotFoundException::new($id);
        }

        $events = array_map(function (array $data) {
            return $this->rowToDomainEvent($data);
        }, $results);

        return new DomainEventStream(...$events);
    }

    function rowToDomainEvent(array $data): DomainEvent
    {
        $class = $data['event_class'];

        return new $class(
            new Uuid($data['aggregate_id']),
            Serializer::deserialize(json_decode($data['payload'], true, 512, JSON_THROW_ON_ERROR)),
            new PlayHead($data['playhead']),
            new DateTimeImmutable(date(DATE_ATOM, strtotime($data['recorded_at']))),
            Serializer::deserialize(json_decode($data['metadata'], true, 512, JSON_THROW_ON_ERROR)),
            new Uuid($data['id']),
        );
    }
}
