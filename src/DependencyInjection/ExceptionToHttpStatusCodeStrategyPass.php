<?php

namespace SharedBundle\DependencyInjection;

use Shared\EventHandling\EventListenerInterface;
use SharedBundle\UI\Http\Rest\EventSubscriber\ExceptionSubscriber;
use SharedBundle\UI\Http\Rest\Exception\ExceptionToHttpStatusCodeMapping;
use SharedBundle\UI\Http\Rest\Exception\Strategy\ExceptionToHttpStatusCodeStrategyInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;

final class ExceptionToHttpStatusCodeStrategyPass implements CompilerPassInterface
{
    #[\Override]
    public function process(ContainerBuilder $container): void
    {
        $exceptionMapping = $container->getDefinition(ExceptionToHttpStatusCodeMapping::class);

        try {
            foreach (array_keys($container->findTaggedServiceIds('packages.shared.exception_subscriber.exception_to_http_status_code')) as $id) {
                $def = $container->getDefinition($id);
                $class = $container->getParameterBag()->resolveValue($def->getClass());

                $r = $container->getReflectionClass($class);

                if (!$r->implementsInterface(ExceptionToHttpStatusCodeStrategyInterface::class)) {
                    throw new \InvalidArgumentException(sprintf('Service "%s" must implement interface "%s".', $id, ExceptionToHttpStatusCodeStrategyInterface::class));
                }

                $exceptionMapping->addArgument($def);
            }
        } catch (ServiceNotFoundException) {
        }
    }
}
