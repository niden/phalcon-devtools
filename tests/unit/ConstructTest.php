<?php

declare(strict_types=1);

namespace Phalcon\DevTools\Tests\Unit;

use Codeception\Test\Unit;
use Phalcon\DevTools\Access\Manager;
use Phalcon\DevTools\Builder\Component\AbstractComponent;
use Phalcon\DevTools\Builder\Component\AllModels;
use Phalcon\DevTools\Builder\Component\Controller;
use Phalcon\DevTools\Builder\Component\Model;
use Phalcon\DevTools\Builder\Component\Module;
use Phalcon\DevTools\Builder\Component\Project;
use Phalcon\DevTools\Builder\Component\Scaffold;
use Phalcon\DevTools\Builder\Project\AbstractProjectBuilder;
use Phalcon\DevTools\Builder\Project\Cli;
use Phalcon\DevTools\Builder\Project\Micro;
use Phalcon\DevTools\Builder\Project\Modules;
use Phalcon\DevTools\Builder\Project\Simple;
use Phalcon\DevTools\Providers\AccessManagerProvider;
use Phalcon\DevTools\Providers\AnnotationsProvider;
use Phalcon\DevTools\Providers\AssetsProvider;
use Phalcon\DevTools\Providers\AssetsResourceProvider;
use Phalcon\DevTools\Providers\ConfigProvider;
use Phalcon\DevTools\Providers\DatabaseProvider;
use Phalcon\DevTools\Providers\DataCacheProvider;
use Phalcon\DevTools\Providers\DbUtilsProvider;
use Phalcon\DevTools\Providers\DispatcherProvider;
use Phalcon\DevTools\Providers\EventsManagerProvider;
use Phalcon\DevTools\Providers\FileSystemProvider;
use Phalcon\DevTools\Providers\FlashSessionProvider;
use Phalcon\DevTools\Providers\LoggerProvider;
use Phalcon\DevTools\Providers\ModelsCacheProvider;
use Phalcon\DevTools\Providers\RegistryProvider;
use Phalcon\DevTools\Providers\RouterProvider;
use Phalcon\DevTools\Providers\SessionProvider;
use Phalcon\DevTools\Providers\SystemInfoProvider;
use Phalcon\DevTools\Providers\TagProvider;
use Phalcon\DevTools\Providers\UrlProvider;
use Phalcon\DevTools\Providers\ViewCacheProvider;
use Phalcon\DevTools\Providers\VoltProvider;
use Phalcon\Di\Injectable;
use Phalcon\Di\ServiceProviderInterface;

final class ConstructTest extends Unit
{
    /**
     * @dataProvider providerObjects
     *
     * @return void
     */
    public function testConstructor(string $source, string $expected): void
    {
        $class = $this->createMock($source);

        $this->assertInstanceOf($expected, $class);
    }

    public function providerObjects(): array
    {
        return [
            [Manager::class, Injectable::class],
            [AllModels::class, AbstractComponent::class],
            [Controller::class, AbstractComponent::class],
            [Model::class, AbstractComponent::class],
            [Module::class, AbstractComponent::class],
            [Project::class, AbstractComponent::class],
            [Scaffold::class, AbstractComponent::class],
            [Cli::class, AbstractProjectBuilder::class],
            [Micro::class, AbstractProjectBuilder::class],
            [Modules::class, AbstractProjectBuilder::class],
            [Simple::class, AbstractProjectBuilder::class],
            [AccessManagerProvider::class, ServiceProviderInterface::class],
            [AnnotationsProvider::class, ServiceProviderInterface::class],
            [AssetsProvider::class, ServiceProviderInterface::class],
            [AssetsResourceProvider::class, ServiceProviderInterface::class],
            [ConfigProvider::class, ServiceProviderInterface::class],
            [DatabaseProvider::class, ServiceProviderInterface::class],
            [DataCacheProvider::class, ServiceProviderInterface::class],
            [DbUtilsProvider::class, ServiceProviderInterface::class],
            [DispatcherProvider::class, ServiceProviderInterface::class],
            [EventsManagerProvider::class, ServiceProviderInterface::class],
            [FileSystemProvider::class, ServiceProviderInterface::class],
            [FlashSessionProvider::class, ServiceProviderInterface::class],
            [LoggerProvider::class, ServiceProviderInterface::class],
            [ModelsCacheProvider::class, ServiceProviderInterface::class],
            [RegistryProvider::class, ServiceProviderInterface::class],
            [RouterProvider::class, ServiceProviderInterface::class],
            [SessionProvider::class, ServiceProviderInterface::class],
            [SystemInfoProvider::class, ServiceProviderInterface::class],
            [TagProvider::class, ServiceProviderInterface::class],
            [UrlProvider::class, ServiceProviderInterface::class],
            [ViewCacheProvider::class, ServiceProviderInterface::class],
            [ViewCacheProvider::class, ServiceProviderInterface::class],
            [VoltProvider::class, ServiceProviderInterface::class],
        ];
    }
}
