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

namespace Phalcon\DevTools;

use Phalcon\DevTools\Error\ErrorHandler;
use Phalcon\DevTools\Exception\InvalidArgumentException;
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
use Phalcon\DevTools\Providers\ViewProvider;
use Phalcon\DevTools\Providers\VoltProvider;
use Phalcon\Di\Di;
use Phalcon\Di\DiInterface;
use Phalcon\Di\FactoryDefault;
use Phalcon\Di\ServiceProviderInterface;
use Phalcon\Mvc\Application as MvcApplication;

use function array_combine;
use function basename;
use function constant;
use function defined;
use function in_array;
use function method_exists;
use function rtrim;
use function set_time_limit;
use function str_replace;
use function strlen;
use function strpos;
use function strtolower;
use function substr;
use function trim;

use const PHP_SAPI;

/**
 * @method mixed getShared($name, $parameters = null)
 * @method mixed get($name, $parameters = null)
 */
class Bootstrap
{
    /**
     * Application instance.
     *
     * @var MvcApplication
     */
    protected MvcApplication $app;
    /**
     * The path where the project was created.
     *
     * @var string
     */
    protected string $basePath = '';
    /**
     * Configurable parameters
     *
     * @var array
     */
    protected array $configurable = [
        'ptools_path',
        'ptools_ip',
        'base_path',
        'host_name',
        'templates_path',
    ];
    /**
     * Parameters that can be set using constants
     *
     * @var array
     */
    protected array $defines = [
        'PTOOLSPATH',
        'PTOOLS_IP',
        'BASE_PATH',
        'HOSTNAME',
        'TEMPLATE_PATH',
    ];
    /**
     * The services container.
     *
     * @var DiInterface
     */
    protected DiInterface $di;
    /**
     * The current hostname.
     *
     * @var string
     */
    protected string $hostName = 'Unknown';
    /**
     * @var array
     */
    protected array $loaders = [
        'web' => [
            AccessManagerProvider::class,
            EventsManagerProvider::class,
            ConfigProvider::class,
            LoggerProvider::class,
            DataCacheProvider::class,
            ModelsCacheProvider::class,
            ViewCacheProvider::class,
            VoltProvider::class,
            ViewProvider::class,
            AnnotationsProvider::class,
            RouterProvider::class,
            UrlProvider::class,
            TagProvider::class,
            DispatcherProvider::class,
            AssetsProvider::class,
            SessionProvider::class,
            FlashSessionProvider::class,
            DatabaseProvider::class,
            RegistryProvider::class,
            FileSystemProvider::class,
            DbUtilsProvider::class,
            SystemInfoProvider::class,
            AssetsResourceProvider::class,
        ],
        'cli' => [
            // @todo
        ],
    ];
    /**
     * The current application mode.
     *
     * @var string
     */
    protected string $mode = 'web';
    /**
     * The allowed IP for access.
     *
     * @var string
     */
    protected string $ptoolsIp = '';
    /**
     * The path to the Phalcon Developers Tools.
     *
     * @var string
     */
    protected string $ptoolsPath = '';
    /**
     * The DevTools templates path.
     *
     * @var string
     */
    protected string $templatesPath = '';

    /**
     * Bootstrap constructor.
     *
     * @param array $parameters
     */
    public function __construct(array $parameters = [])
    {
        $this->defines = array_combine($this->defines, $this->configurable);

        $this->initFromConstants();
        $this->setParameters($parameters);

        $this->di  = new FactoryDefault();
        $this->app = new MvcApplication();
        $this->di->setShared('application', $this);

        (new ErrorHandler())->register();

        foreach ($this->loaders[$this->mode] as $providerClass) {
            /** @var ServiceProviderInterface $provider */
            $provider = new $providerClass();
            $provider->register($this->di);
        }

        $this->app->setEventsManager($this->di->getShared('eventsManager'));
        $this->app->setDI($this->di);

        Di::setDefault($this->di);
    }

    /**
     * Gets the path where the project was created.
     *
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    public function getCurrentUri(): string
    {
        $baseUrl = $this->di->getShared('url')->getBaseUri();

        return str_replace(
            basename($_SERVER['SCRIPT_FILENAME']),
            '',
            substr($_SERVER['REQUEST_URI'], strpos($_SERVER['REQUEST_URI'], $baseUrl) + strlen($baseUrl))
        );
    }

    /**
     * Gets the current application mode.
     *
     * @return string
     */
    public function getHostName(): string
    {
        return $this->hostName;
    }

    /**
     * Gets the current application mode.
     *
     * @return string
     */
    public function getMode(): string
    {
        return $this->mode;
    }

    /**
     * Get application output.
     *
     * @return string
     */
    public function getOutput(): string
    {
        return $this->app->handle($this->getCurrentUri())->getContent();
    }

    /**
     * Gets the allowed IP for access.
     *
     * @return string
     */
    public function getPtoolsIp(): string
    {
        return $this->ptoolsIp;
    }

    /**
     * Gets the path to the Phalcon Developers Tools.
     *
     * @return string
     */
    public function getPtoolsPath(): string
    {
        return $this->ptoolsPath;
    }

    /**
     * Gets the DevTools templates path.
     *
     * @return string
     */
    public function getTemplatesPath(): string
    {
        return $this->templatesPath;
    }

    /**
     * Sets the params by using defined constants.
     *
     * @return $this
     */
    public function initFromConstants(): Bootstrap
    {
        foreach ($this->defines as $property) {
            if (defined($property) && in_array($property, $this->configurable, true)) {
                $this->setParameter($property, constant($property));
            }
        }

        return $this;
    }

    /**
     * Runs the Application.
     *
     * @return MvcApplication|string
     */
    public function run()
    {
        if (PHP_SAPI === 'cli') {
            set_time_limit(0);
        }

        if (ENV_TESTING === APPLICATION_ENV) {
            return $this->app;
        }

        return $this->getOutput();
    }

    /**
     * Sets the path where the project was created.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setBasePath(string $path): Bootstrap
    {
        $this->basePath = rtrim($path, '\\/');

        return $this;
    }

    /**
     * Sets the current hostname.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setHostName(string $name): Bootstrap
    {
        $this->hostName = trim($name);

        return $this;
    }

    /**
     * Sets the current application mode.
     *
     * @param string $mode
     *
     * @return $this
     */
    public function setMode(string $mode): Bootstrap
    {
        $mode = strtolower(trim($mode));

        if (isset($this->loaders[$mode])) {
            $mode = 'web';
        }

        $this->mode = $mode;

        return $this;
    }

    /**
     * Sets the parameter by using snake_case notation.
     *
     * @param string $parameter Parameter name
     * @param mixed  $value     The value
     *
     * @return $this
     */
    public function setParameter(string $parameter, $value): Bootstrap
    {
        $method = 'set' . Utils::camelize($parameter);

        if (method_exists($this, $method)) {
            $this->$method($value);
        }

        return $this;
    }

    /**
     * Sets the params by using passed config.
     *
     * @param array $parameters
     *
     * @return $this
     * @throws InvalidArgumentException
     */
    public function setParameters(array $parameters): Bootstrap
    {
        foreach ($this->configurable as $param) {
            if (!isset($parameters[$param])) {
                continue;
            }

            $this->setParameter($param, $parameters[$param]);
        }

        return $this;
    }

    /**
     * Sets the allowed IP for access.
     *
     * @param string $ip
     *
     * @return $this
     */
    public function setPtoolsIp(string $ip): Bootstrap
    {
        $this->ptoolsIp = trim($ip);

        return $this;
    }

    /**
     * Sets the path to the Phalcon Developers Tools.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setPtoolsPath(string $path): Bootstrap
    {
        $this->ptoolsPath = rtrim($path, '\\/');

        return $this;
    }

    /**
     * Sets the DevTools templates path.
     *
     * @param string $path
     *
     * @return $this
     */
    public function setTemplatesPath(string $path): Bootstrap
    {
        $this->templatesPath = rtrim($path, '\\/');

        return $this;
    }
}
