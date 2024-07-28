<?php

declare(strict_types=1);

namespace SharedBundle\MongoDB\Doctrine\Type\ODM;

use Doctrine\ODM\MongoDB\Types\ClosureToPHP;
use Doctrine\ODM\MongoDB\Types\Type;
use Shared\Domain\Uuid;

final class UuidType extends Type
{
    use ClosureToPHP;

    public const string NAME = 'uuid';

    #[\Override]
    public function convertToDatabaseValue($value): ?string
    {
        if (null === $value || \is_string($value)) {
            return $value;
        }

        if ($value instanceof Uuid) {
            return $value->uuid;
        }

        throw new \RuntimeException('Could not convert database value "' . $value . '" to MongoDB Type ' . self::NAME);
    }

    #[\Override]
    public function convertToPHPValue($value): ?Uuid
    {
        if (null === $value || $value instanceof Uuid) {
            return $value;
        }

        try {
            return new Uuid($value);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Could not convert database value "' . $value . '" to MongoDB Type ' . self::NAME, 0, $exception);
        }
    }
}
