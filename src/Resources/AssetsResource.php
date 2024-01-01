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

namespace Phalcon\DevTools\Resources;

use Phalcon\DevTools\Utils\FsUtils;
use Phalcon\Di\Injectable;

use const DIRECTORY_SEPARATOR;

/**
 * @property FsUtils $fs
 */
class AssetsResource extends Injectable
{
    /**
     * Returns assets resource path.
     *
     * @param string $path
     *
     * @return string
     */
    public function path(string $path): string
    {
        return PTOOLSPATH . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . $this->fs->normalize($path);
    }
}
