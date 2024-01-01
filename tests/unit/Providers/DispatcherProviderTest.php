<?php

declare(strict_types=1);

namespace Phalcon\DevTools\Tests\Unit\Providers;

use Codeception\Test\Unit;
use Phalcon\DevTools\Providers\DispatcherProvider;
use Phalcon\Di\ServiceProviderInterface;

final class DispatcherProviderTest extends Unit
{
    public function testImplementation(): void
    {
        $class = $this->createMock(DispatcherProvider::class);

        $this->assertInstanceOf(ServiceProviderInterface::class, $class);
    }
}
