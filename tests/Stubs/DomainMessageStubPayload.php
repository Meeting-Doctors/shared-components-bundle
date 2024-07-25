<?php

namespace SharedBundle\Tests\Stubs;

use Shared\Domain\PayloadInterface;
use Shared\Serializer\SerializableInterface;

class DomainMessageStubPayload implements PayloadInterface
{
    public static function deserialize(array $data): SerializableInterface
    {
        return new self();
    }

    public function serialize(): array
    {
        return [];
    }
}