<?php

declare(strict_types=1);

namespace SharedBundle\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Shared\EventHandling\EventBusInterface;
use SharedBundle\SharedBundle;
use SharedBundle\Tests\Stubs\Testing\Command\AHandler as CommandAHandler;
use SharedBundle\Tests\Stubs\Testing\Command\ThrowableHandler as CommandThrowableHandler;
use SharedBundle\Tests\Stubs\Testing\Query\AHandler;
use SharedBundle\Tests\Stubs\Testing\Query\AnotherHandler;
use SharedBundle\Tests\Stubs\Testing\Query\ThrowableHandler;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

final class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    #[\Override]
    public function registerBundles(): iterable
    {
        return [
            new DoctrineBundle(),
            new FrameworkBundle(),
            new SharedBundle(),
        ];
    }

    #[\Override]
    public function registerContainerConfiguration(LoaderInterface $loader): void
    {
        $loader->load(static function (ContainerBuilder $container) {
            $container->loadFromExtension('doctrine', [
                'dbal' => [
                    'driver' => 'pdo_mysql',
                    'charset' => 'utf8mb4',
                    'url' => getenv('DATABASE_URL'),
                ],
                'orm' => [
                    'auto_generate_proxy_classes' => true,
                    'auto_mapping' => true,
                    'naming_strategy' => 'doctrine.orm.naming_strategy.underscore_number_aware',
                ],
            ]);

            $container->loadFromExtension('framework', [
                'secret' => 'nope',
                'test' => null,
                'http_method_override' => true,
                'php_errors' => ['log' => true],
            ]);

            if (!$container->hasDefinition('kernel')) {
                $container
                    ->register('kernel', self::class)
                    ->setSynthetic(true)
                    ->setPublic(true);
            }

            $container->register('CommandThrowableHandler', CommandThrowableHandler::class)
                ->addTag('messenger.message_handler', ['bus' => 'messenger.bus.command']);

            $container->register('ACommandHandler', CommandAHandler::class)
                ->addArgument(new Reference(EventBusInterface::class))
                ->addTag('messenger.message_handler', ['bus' => 'messenger.bus.command']);

            $container->register('QueryThrowableHandler', ThrowableHandler::class)
                ->addTag('messenger.message_handler', ['bus' => 'messenger.bus.query']);
            $container->register('AQueryHandler', AHandler::class)
                ->addTag('messenger.message_handler', ['bus' => 'messenger.bus.query']);
            $container->register('AnotherQueryHandler', AnotherHandler::class)
                ->addTag('messenger.message_handler', ['bus' => 'messenger.bus.query']);
        });
    }
}
