<?php

declare(strict_types=1);

namespace SharedBundle\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception as DBALException;
use Doctrine\DBAL\Exception\ConstraintViolationException;
use Doctrine\DBAL\Types\Types;
use Shared\Domain\DateTimeImmutable;
use Shared\Domain\DomainEvent;
use Shared\Domain\DomainEventStream;
use Shared\Domain\Playhead;
use Shared\Domain\Uuid;
use Shared\EventStore\DomainEventStreamNotFoundException;
use Shared\EventStore\EventStoreException;
use Shared\EventStore\EventStoreInterface;
use Shared\EventStore\EventStoreManagerInterface;
use Shared\EventStore\EventVisitorInterface;
use Shared\EventStore\PlayheadAlreadyExistsException;
use Shared\Serializer\Serializer;
use SharedBundle\Criteria\CriteriaConverterException;

final readonly class DBALEventStore implements EventStoreInterface, EventStoreManagerInterface
{
    private const string DATETIME_FORMAT = 'Y-m-d H:i:s.u';

    public const string TABLE_NAME = 'domain_events';
    public const string TABLE_SCHEMA = 'CREATE TABLE IF NOT EXISTS `'.self::TABLE_NAME.'` (
        `id` VARBINARY(16) NOT NULL,
        `aggregate_id` VARBINARY(16),
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
        FROM `'.self::TABLE_NAME.'`
        WHERE (`aggregate_id` = :aggregate_id)
        ORDER BY `playhead` ASC
';
    public const SQL_GET_WITH_PLAYHEAD = 'SELECT * 
        FROM `'.self::TABLE_NAME.'`
        WHERE (`aggregate_id` = :aggregate_id)
        AND (`playhead` >= :playhead)
        ORDER BY `playhead` ASC
';

    public function __construct(private Connection $connection)
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
                'aggregate_id' => $this->uuidToVarBinary($id),
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
                        'id' => $this->uuidToVarBinary($event->id()),
                        'aggregate_id' => $this->uuidToVarBinary($event->aggregateId()),
                        'event_class' => $event::class,
                        'payload' => json_encode(Serializer::serialize($event->payload()), JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION),
                        'playhead' => $event->playHead()->value,
                        'metadata' => json_encode(Serializer::serialize($event->metadata()), JSON_THROW_ON_ERROR | JSON_PRESERVE_ZERO_FRACTION),
                        'recorded_at' => $event->recordedAt()->format(self::DATETIME_FORMAT),
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
                'aggregate_id' => $this->uuidToVarBinary($aggregateId),
            ],
            [
                'aggregate_id' => Types::STRING,
            ]
        );

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
                'aggregate_id' => $this->uuidToVarBinary($id),
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

    public function rowToDomainEvent(array $data): DomainEvent
    {
        $class = $data['event_class'];

        return new $class(
            $this->varBinaryToUuid($data['aggregate_id']),
            Serializer::deserialize(json_decode($data['payload'], true, 512, JSON_THROW_ON_ERROR)),
            new Playhead($data['playhead']),
            DateTimeImmutable::fromFormat(self::DATETIME_FORMAT, $data['recorded_at']),
            Serializer::deserialize(json_decode($data['metadata'], true, 512, JSON_THROW_ON_ERROR)),
            $this->varBinaryToUuid($data['id']),
        );
    }

    private function uuidToVarBinary(Uuid $uuid): string
    {
        return hex2bin(str_replace('-', '', $uuid->uuid));
    }

    private function varBinaryToUuid(string $string): Uuid
    {
        $uuid = bin2hex($string);

        $value = sprintf(
            '%s-%s-%s-%s-%s',
            substr($uuid, 0, 8),
            substr($uuid, 8, 4),
            substr($uuid, 12, 4),
            substr($uuid, 16, 4),
            substr($uuid, 20)
        );

        return new Uuid($value);
    }
}
