<?php

declare(strict_types=1);

namespace SharedBundle\UI\Http\Rest\Controller;

use Shared\CommandHandling\CommandBusInterface;
use Shared\CommandHandling\CommandInterface;

abstract readonly class AbstractCommandController
{
    public function __construct(
        private CommandBusInterface $commandBus
    ) {
    }

    final protected function handle(CommandInterface $command): void
    {
        $this->commandBus->handle($command);
    }
}
