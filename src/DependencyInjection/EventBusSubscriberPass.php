<?php

declare(strict_types=1);

namespace SharedBundle\DependencyInjection;

use Shared\EventHandling\EventBusInterface;
use Shared\EventHandling\EventListenerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\DependencyInjection\Reference;

final class EventBusSubscriberPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        try {
            $definition = $container->getDefinition(EventBusInterface::class);

            foreach (array_keys($container->findTaggedServiceIds('packages.shared.event_handling.event_listener')) as $id) {
                $def = $container->getDefinition($id);
                $class = $container->getParameterBag()->resolveValue($def->getClass());

                $r = new \ReflectionClass($class);

                if (!$r->implementsInterface(EventListenerInterface::class)) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, EventListenerInterface::class));
                }

                $definition->addMethodCall('subscribe', [new Reference($id)]);
            }
        } catch (ServiceNotFoundException) {
        }
    }
}
