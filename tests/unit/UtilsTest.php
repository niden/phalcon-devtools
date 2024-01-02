<?php

/**
 * This file is part of the Phalcon Developer Tools.
 *
 * (c) Phalcon Team <team@phalcon.io>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

declare(strict_types=1);

namespace Phalcon\DevTools\Tests\Unit;

use Phalcon\DevTools\Utils;
use Phalcon\DevTools\Tests\Support\Module\UnitTest;

final class UtilsTest extends UnitTest
{
    /**
     * Tests Utils::camelize
     *
     * @dataProvider providerCamelizeString
     *
     * @issue  1056
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-08-02
     */
    public function testCamelizeString(
        string $source,
        string $delimiter,
        string $expected
    ): void {
        $actual = Utils::camelize($source, $delimiter);
        $this->assertSame($actual, $expected);
    }

    /**
     * Tests Utils::lowerCamelizeWithDelimiter
     *
     * @dataProvider providerCamelizeStringWithDelimiter
     *
     * @issue  1070
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-08-07
     */
    public function shouldCamelizeStringWithDelimiter(
        string $source,
        string $delimiter,
        bool $useLower,
        string $expected
    ): void {
        $actual = Utils::lowerCamelizeWithDelimiter($source, $delimiter, $useLower);
        $this->assertSame($actual, $expected);
    }

    /**
     * Tests Utils::lowerCamelize
     *
     * @test
     * @author Sergii Svyrydenko <sergey.v.sviridenko@gmail.com>
     * @since  2017-08-02
     */
    public function shouldLowercamelizeString()
    {
        $source = 'MyFooBar';
        $expected = 'myFooBar';
        $actual = Utils::lowerCamelize($source);
        $this->assertSame($actual, $expected);
    }

    /**
     * @return array[]
     */
    public function providerCamelizeString(): array
    {
        return [
            ['MyFooBar', '_', 'MyFooBar'],
            ['MyFooBar', '_-', 'MyFooBar'],
            ['My-Foo_Bar', '-', 'MyFoo_Bar'],
            ['My-Foo_Bar', '_-', 'MyFooBar'],
        ];
    }

    /**
     * @return array[]
     */
    public function providerCamelizeStringWithDelimiter(): array
    {
        return [
            ['myfoobar', '_', false, 'myfoobar'],
            ['myfoobar', '_-', false, 'Myfoobar'],
            ['My-Foo_Bar', '_-', false, 'MyFooBar'],
            ['my-foo_bar', '_-', false, 'MyFooBar'],
            ['my-foo_bar', '_-', true, 'myFooBar'],
        ];
    }
}
