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

namespace Phalcon\DevTools\Scanners;

use Phalcon\Config\Adapter\Ini as IniConfig;
use Phalcon\Config\Adapter\Json as JsonConfig;
use Phalcon\Config\Adapter\Yaml as YamlConfig;
use Phalcon\Config\Config as PhConfig;
use Phalcon\Config\Exception;
use Phalcon\DevTools\Utils\FsUtils;
use Phalcon\Di\Injectable;

use function array_map;
use function implode;
use function in_array;
use function is_array;
use function is_dir;
use function is_file;
use function is_readable;
use function is_string;
use function pathinfo;
use function rtrim;
use function sprintf;

use const DIRECTORY_SEPARATOR;
use const PATHINFO_FILENAME;

class Config extends Injectable
{
    /**
     * @var string
     */
    protected string $basePath = '';
    /**
     * @var array
     */
    protected array $configAdapters = [
        'ini'  => IniConfig::class,
        'json' => JsonConfig::class,
        'php'  => PhConfig::class,
        'php5' => PhConfig::class,
        'inc'  => PhConfig::class,
        'yml'  => YamlConfig::class,
        'yaml' => YamlConfig::class,
    ];
    /**
     * @var array
     */
    protected array $configDirs = [
        'config',
        'app/config',
        'apps/config',
        'app/frontend/config',
        'apps/frontend/config',
        'app/backend/config',
        'apps/backend/config',
    ];

    public function __construct($basePath)
    {
        if (is_string($basePath)) {
            $this->basePath = rtrim($basePath, '\\/');
        }
    }

    /**
     * Prepares config paths.
     *
     * @return array
     */
    public function getConfigPaths(): array
    {
        /** @var FsUtils $fsUtils */
        $fsUtils  = $this->getDI()->getShared('fs');
        $basePath = $this->basePath;

        if (!is_dir($basePath) || !is_readable($basePath)) {
            return [];
        }

        return array_map(function ($val) use ($basePath, $fsUtils) {
            return $basePath . $fsUtils->normalize("/{$val}");
        }, $this->configDirs);
    }

    /**
     * Alias for Config::scan but throws Exception if configuration could not be found.
     *
     * @param string $filename The config basename.
     *
     * @return PhConfig
     * @throws Exception
     */
    public function load(string $filename): PhConfig
    {
        $config = $this->scan($filename);

        if (!$config instanceof PhConfig) {
            throw new Exception(
                sprintf(
                    "Configuration file couldn't be loaded! Scanned paths: %s",
                    implode(', ', $this->getConfigPaths())
                )
            );
        }

        return $config;
    }

    /**
     * Scans for application config.
     *
     * @param string $filename The config basename.
     *
     * @return null|PhConfig
     */
    public function scan(string $filename): ?PhConfig
    {
        $config   = null;
        $filename = pathinfo($filename, PATHINFO_FILENAME);

        foreach ($this->getConfigPaths() as $probablyPath) {
            foreach ($this->configAdapters as $ext => $adapter) {
                $probablyConfig = $probablyPath . DIRECTORY_SEPARATOR . "{$filename}.{$ext}";

                if (is_file($probablyConfig) && is_readable($probablyConfig)) {
                    if (in_array($ext, ['php', 'php5', 'inc'])) {
                        /** @noinspection PhpIncludeInspection */
                        $config = include($probablyConfig);
                        if (is_array($config)) {
                            $config = new PhConfig($config);
                        }
                    } else {
                        $config = new $adapter($probablyConfig);
                    }

                    break(2);
                }
            }
        }

        return $config;
    }
}
