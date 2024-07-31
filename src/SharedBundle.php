<?php

declare(strict_types=1);

namespace SharedBundle;

use SharedBundle\DependencyInjection\EventBusSubscriberPass;
use SharedBundle\DependencyInjection\ExceptionToHttpStatusCodeStrategyPass;
use SharedBundle\DependencyInjection\SharedExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class SharedBundle extends AbstractBundle
{
    #[\Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new EventBusSubscriberPass());
        $container->addCompilerPass(new ExceptionToHttpStatusCodeStrategyPass());
    }

    #[\Override]
    public function getContainerExtension(): ExtensionInterface
    {
        return new SharedExtension();
    }
}
