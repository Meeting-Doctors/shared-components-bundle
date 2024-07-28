<?php

declare(strict_types=1);

namespace SharedBundle\MongoDB\Doctrine\Type\ODM;

use Doctrine\ODM\MongoDB\Types\ClosureToPHP;
use Doctrine\ODM\MongoDB\Types\Type;
use Shared\Domain\NotEmptyString;

final class NotEmptyStringType extends Type
{
    use ClosureToPHP;

    public const string NAME = 'not_empty_string';

    #[\Override]
    public function convertToDatabaseValue($value): ?string
    {
        if (null === $value || \is_string($value)) {
            return $value;
        }

        if ($value instanceof NotEmptyString) {
            return $value->string;
        }

        throw new \RuntimeException('Could not convert database value "' . $value . '" to MongoDB Type ' . self::NAME);
    }

    #[\Override]
    public function convertToPHPValue($value): ?NotEmptyString
    {
        if (null === $value || $value instanceof NotEmptyString) {
            return $value;
        }

        try {
            return new NotEmptyString($value);
        } catch (\Throwable $exception) {
            throw new \RuntimeException('Could not convert database value "' . $value . '" to MongoDB Type ' . self::NAME, 0, $exception);
        }
    }
}
