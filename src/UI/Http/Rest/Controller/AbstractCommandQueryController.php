<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Controller;

use Shared\CommandHandling\CommandBusInterface;
use Shared\CommandHandling\CommandInterface;
use Shared\CommandHandling\QueryBusInterface;

abstract readonly class AbstractCommandQueryController extends AbstractQueryController
{
    public function __construct(
        private CommandBusInterface $commandBus,
        QueryBusInterface $queryBus
    ) {
        parent::__construct($queryBus);
    }

    final protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
