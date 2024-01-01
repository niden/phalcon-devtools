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

namespace Phalcon\DevTools\Commands\Builtin;

use Phalcon\DevTools\Commands\Command;
use Phalcon\DevTools\Script\Color;

use function join;
use function str_repeat;
use function strlen;

use const PHP_EOL;

/**
 * Enumerate Command
 */
class Enumerate extends Command
{
    public const COMMAND_COLUMN_LEN = 16;

    /**
     * {@inheritdoc}
     *
     * @return bool
     */
    public function canBeExternal(): bool
    {
        return true;
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getCommands(): array
    {
        return ['commands', 'list', 'enumerate'];
    }

    /**
     * {@inheritdoc}
     *
     * @return void
     */
    public function getHelp(): void
    {
        print Color::head('Help:') . PHP_EOL;
        print Color::colorize('  Lists the commands available in Phalcon DevTools') . PHP_EOL . PHP_EOL;

        $this->run([]);
    }

    /**
     * {@inheritdoc}
     *
     * @return array
     */
    public function getPossibleParams(): array
    {
        return [
            'help' => 'Shows this help [optional]',
        ];
    }

    /**
     * {@inheritdoc}
     *
     * @return int
     */
    public function getRequiredParams(): int
    {
        return 0;
    }

    /**
     * {@inheritdoc}
     *
     * @param array $parameters
     */
    public function run(array $parameters): void
    {
        print Color::colorize('Available commands:', Color::FG_BROWN) . PHP_EOL;
        foreach ($this->getScript()->getCommands() as $commands) {
            $providedCommands = $commands->getCommands();
            $commandLen       = strlen($providedCommands[0]);

            print '  ' . Color::colorize($providedCommands[0], Color::FG_GREEN);
            unset($providedCommands[0]);
            if (count($providedCommands)) {
                $spacer = str_repeat(' ', self::COMMAND_COLUMN_LEN - $commandLen);
                print $spacer . ' (alias of: ' . Color::colorize(implode(', ', $providedCommands)) . ')';
            }

            print PHP_EOL;
        }

        print PHP_EOL;
    }
}
