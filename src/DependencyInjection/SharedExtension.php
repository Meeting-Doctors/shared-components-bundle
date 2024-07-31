<?php

declare(strict_types=1);

namespace SharedBundle\DependencyInjection;

use SharedBundle\MongoDB\Doctrine\Type\ODM\NotEmptyStringType as ODMNotEmptyStringType;
use SharedBundle\MongoDB\Doctrine\Type\ODM\UuidType as ODMUuidType;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

final class SharedExtension extends Extension implements PrependExtensionInterface
{
    #[\Override]
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load('services.xml');
    }

    #[\Override]
    public function prepend(ContainerBuilder $container): void
    {
        $this->prependDoctrineConfig($container);
        $this->prependFrameworkConfig($container);
    }

    private function prependDoctrineConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('doctrine_mongodb', [
            'types' => [
                ODMNotEmptyStringType::NAME => ODMNotEmptyStringType::class,
                ODMUuidType::NAME => ODMUuidType::class,
            ]
        ]);
    }

    private function prependFrameworkConfig(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig('framework', [
            'messenger' => [
                'default-bus' => 'messenger.bus.command',
                'buses' => [
                    'messenger.bus.command' => [
                        'default-middleware' => false,
                        'middleware' => [
                            'dispatch_after_current_bus',
                            'doctrine_transaction',
                            'handle_message',
                        ],
                    ],
                    'messenger.bus.query' => [
                        'default-middleware' => false,
                        'middleware' => [
                            'handle_message',
                        ],
                    ],
                    'messenger.bus.event' => [
                        'default-middleware' => [
                            'enabled' => true,
                            'allow_no_handlers' => true,
                        ],
                    ],
                ],
            ],
        ]);
    }
}
