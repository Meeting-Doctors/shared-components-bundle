<?php

declare(strict_types=1);

namespace SharedBundle\DBAL\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\GuidType;
use Doctrine\DBAL\Types\IntegerType;
use Shared\Domain\PlayHead;
use Shared\Domain\Uuid;

final class PlayHeadType extends IntegerType
{
    public const string NAME = 'playhead';

    #[\Override]
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if (null === $value || \is_int($value)) {
            return $value;
        }

        if ($value instanceof PlayHead) {
            return $value->value;
        }

        throw ConversionException::conversionFailed($value, self::NAME);
    }

    #[\Override]
    public function convertToPHPValue($value, AbstractPlatform $platform): ?PlayHead
    {
        if (null === $value || $value instanceof PlayHead) {
            return $value;
        }

        try {
            return new PlayHead($value);
        } catch (\Throwable) {
            throw ConversionException::conversionFailed($value, self::NAME);
        }
    }

    #[\Override]
    public function getName(): string
    {
        return self::NAME;
    }
}
