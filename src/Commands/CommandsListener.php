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

namespace Phalcon\DevTools\Commands;

use Phalcon\Events\Event;

use function file_exists;
use function in_array;
use function is_dir;
use function realpath;

use const DIRECTORY_SEPARATOR;

/**
 * Commands Listener
 */
class CommandsListener
{
    /**
     * Before command executing
     *
     * @param Event   $event
     * @param Command $command
     *
     * @return bool
     * @throws CommandsException
     * @throws DotPhalconMissingException
     */
    public function beforeCommand(Event $event, Command $command): bool
    {
        $parameters = $command->parseParameters([], ['h' => 'help']);

        if (
            count($parameters) < ($command->getRequiredParams() + 1) ||
            $command->isReceivedOption(['help', 'h', '?']) ||
            in_array($command->getOption(1), ['help', 'h', '?'])
        ) {
            $command->getHelp();

            return false;
        }

        if (!$command->canBeExternal()) {
            $path = $command->getOption('directory');
            if ($path) {
                $path = realpath($path) . DIRECTORY_SEPARATOR;
            }

            if (!file_exists($path . '.phalcon') || !is_dir($path . '.phalcon')) {
                throw new DotPhalconMissingException();
            }
        }

        return true;
    }
}
