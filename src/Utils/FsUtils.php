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

namespace Phalcon\DevTools\Utils;

use ArrayIterator;
use DirectoryIterator;
use Iterator;
use Phalcon\DevTools\Exception\InvalidArgumentException;
use Phalcon\Support\Helper\Str\ReduceSlashes;
use RuntimeException;
use SplFileInfo;

use function boolval;
use function chmod;
use function file_exists;
use function function_exists;
use function getenv;
use function iterator_apply;
use function mkdir;
use function posix_getgrgid;
use function posix_getpwuid;
use function preg_match;
use function preg_replace;
use function str_replace;
use function strtoupper;
use function substr;
use function trim;
use function unlink;

use const DS;
use const PHP_OS;

class FsUtils
{
    /**
     * Delete files from directory
     *
     * @param SplFileInfo $root
     * @param array       $files
     */
    public function deleteFilesFromDirectory(SplFileInfo $root, array $files): void
    {
        $iterator = new ArrayIterator($files);
        $cb       = function (Iterator $iterator, $basePath) {
            while ($iterator->valid()) {
                $desiredPath = $basePath . DS . $iterator->current();
                if (file_exists($desiredPath)) {
                    unlink($desiredPath);
                }

                $iterator->next();
            }
        };

        $this->applyWithCallback($iterator, $cb, [$iterator, $root->getRealPath()]);
    }

    /**
     * Return info about a user and group file iterator.
     *
     * @param DirectoryIterator $file
     *
     * @return string
     */
    public function getOwner(DirectoryIterator $file): string
    {
        if (!function_exists('posix_getpwuid')) {
            // Windows, fallback, etc.
            return getenv('USERNAME') ?: (string)getenv('USER');
        }

        $user  = posix_getpwuid($file->getOwner());
        $group = posix_getgrgid($file->getGroup());

        $userName  = !empty($user['name']) ? $user['name'] : '-?-';
        $groupName = !empty($group['name']) ? $group['name'] : '-?-';

        return $userName . ' / ' . $groupName;
    }

    /**
     * Checks whether the path is absolute or not.
     *
     * @param string $path Path to check
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    public function isAbsolute(string $path): bool
    {
        if (empty($path = trim($path))) {
            return false;
        }

        if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
            return boolval(preg_match('/^[A-Z]:\\\\/', $path));
        }

        return substr($path, 0, 1) === DS;
    }

    /**
     * Normalize file path.
     *
     * - Convert all slashes depending on the operating system
     * - Get rid of '..', '.'
     * - Remove self referring paths ('/./')
     * - Remove any kind of funky unicode whitespace
     * - Reduce slashes
     *
     * @param string $path Path to normalize
     *
     * @return string
     * @throws InvalidArgumentException
     */
    public function normalize(string $path): string
    {
        if (empty($path = trim($path))) {
            return '';
        }

        $normalized = preg_replace('#\p{C}+|^\./#u', '', $path);
        $normalized = preg_replace('#/\.(?=/)|^\./|(/|^)\./?$#', '', $normalized);
        $normalized = str_replace(['\\', '/'], DS, $normalized);

        return (new ReduceSlashes())($normalized);
    }

    /**
     * Set permission to public folder
     *
     * @param SplFileInfo $root
     * @param array       $dir
     */
    public function setDirectoryPermission(SplFileInfo $root, array $dir): void
    {
        $this->createRecursiveDirectory($root);
        $iterator = new ArrayIterator($dir);
        $cb       = function (Iterator $iterator, $basePath) {
            while ($iterator->valid()) {
                $desiredPath = $basePath . DS . $iterator->key() . DS;
                if (!file_exists($desiredPath)) {
                    $this->createRecursiveDirectory(new SplFileInfo($desiredPath));
                }
                chmod($desiredPath . DS, $iterator->current());

                $iterator->next();
            }
        };

        $this->applyWithCallback($iterator, $cb, [$iterator, $root->getRealPath()]);
    }

    /**
     * Callback function
     *
     * @param ArrayIterator $iterator
     * @param callable      $cb
     * @param array         $params
     */
    protected function applyWithCallback(ArrayIterator $iterator, $cb, array $params): void
    {
        iterator_apply($iterator, $cb, $params);
    }

    /**
     * Create directory
     *
     * @param SplFileInfo $root
     */
    protected function createRecursiveDirectory(SplFileInfo $root): void
    {
        if ($root->isDir()) {
            return;
        }

        if ($root->isFile()) {
            throw new RuntimeException("A {$root} can't be a file");
        }

        if (!mkdir($root->getPathname(), 0777, true)) {
            throw new RuntimeException("Unable to create {$root} path");
        }
    }
}
