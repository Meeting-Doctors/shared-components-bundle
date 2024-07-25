<?php

declare(strict_types=1);

namespace SharedBundle\Tests\Stubs\Testing\Query;

use Shared\CommandHandling\Item;
use Shared\CommandHandling\QueryHandlerInterface;
use SharedBundle\Tests\Stubs\ReadModelProjectionStub;

final readonly class AnotherHandler implements QueryHandlerInterface
{
    public function __invoke(AnotherQuery $query): Item
    {
        return Item::fromSerializable(ReadModelProjectionStub::deserialize([
            'id' => '9db0db88-3e44-4d2b-b46f-9ca547de06ac',
        ]));
    }
}
