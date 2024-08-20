<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Router\Helper;

use Codeception\Module;
use Codeception\Stub;
use Codeception\TestInterface;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Zed\Router\Business\Loader\ClosureLoader;
use Spryker\Zed\Router\Business\Route\Route;
use Spryker\Zed\Router\Business\Route\RouteCollection;
use Spryker\Zed\Router\Business\Router\Router;
use Spryker\Zed\Router\Business\RouterFacadeInterface;
use Spryker\Zed\Router\Communication\Plugin\Application\RouterApplicationPlugin;
use Spryker\Zed\Router\Communication\Plugin\EventDispatcher\RouterListenerEventDispatcherPlugin;
use Spryker\Zed\Router\Communication\Plugin\Router\ZedRouterPlugin;
use Spryker\Zed\Router\Communication\RouterCommunicationFactory;
use Spryker\Zed\Router\RouterConfig;
use Spryker\Zed\Router\RouterDependencyProvider;
use Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface;
use SprykerTest\Service\Container\Helper\ContainerHelperTrait;
use SprykerTest\Shared\Testify\Helper\ConfigHelperTrait;
use SprykerTest\Shared\Testify\Helper\ModuleHelperConfigTrait;
use SprykerTest\Zed\Application\Helper\ApplicationHelperTrait;
use SprykerTest\Zed\EventDispatcher\Helper\EventDispatcherHelperTrait;
use SprykerTest\Zed\Testify\Helper\Business\BusinessHelperTrait;
use SprykerTest\Zed\Testify\Helper\Communication\CommunicationHelperTrait;
use SprykerTest\Zed\Testify\Helper\Communication\DependencyProviderHelperTrait;
use Symfony\Component\HttpFoundation\Request;

class RouterHelper extends Module
{
    use ApplicationHelperTrait;
    use CommunicationHelperTrait;
    use BusinessHelperTrait;
    use ConfigHelperTrait;
    use DependencyProviderHelperTrait;
    use EventDispatcherHelperTrait;
    use ContainerHelperTrait;
    use ModuleHelperConfigTrait;

    /**
     * @var string
     */
    protected const MODULE_NAME = 'Router';

    /**
     * @var string
     */
    protected const CONFIG_KEY_ROUTER_PLUGINS = 'routerPlugins';

    /**
     * @var array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected $routerPlugins = [];

    /**
     * @uses \Spryker\Zed\Router\Communication\Plugin\Application\RouterApplicationPlugin::SERVICE_ROUTER
     *
     * @var string
     */
    protected const SERVICE_ROUTER = 'routers';

    /**
     * @var \Spryker\Zed\Router\Business\Route\RouteCollection|null
     */
    protected $routeCollection;

    /**
     * @var \Spryker\Zed\Router\Communication\Plugin\Router\ZedRouterPlugin|null
     */
    protected static $routerPlugin;

    /**
     * @return void
     */
    public function _initialize(): void
    {
        foreach ($this->config[static::CONFIG_KEY_ROUTER_PLUGINS] as $routerPlugin) {
            $this->routerPlugins[$routerPlugin] = new $routerPlugin();
        }

        if (!isset($this->routerPlugins[ZedRouterPlugin::class])) {
            $this->routerPlugins[ZedRouterPlugin::class] = $this->getRouterPlugin();
        }

        $requestFactory = function (array $query = [], array $request = [], array $attributes = [], array $cookies = [], array $files = [], array $server = [], $content = null) {
            $request = new Request($query, $request, $attributes, $cookies, $files, $server, $content);
            $request->server->set('SERVER_NAME', 'localhost');

            return $request;
        };
        Request::setFactory($requestFactory);
    }

    /**
     * @return void
     */
    protected function setDefaultConfig(): void
    {
        $this->config = [
            static::CONFIG_KEY_ROUTER_PLUGINS => [],
        ];
    }

    /**
     * @return \Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface
     */
    protected function getRouterPlugin(): RouterPluginInterface
    {
        if (static::$routerPlugin === null) {
            $controllerDirectories = sprintf('%s/spryker/spryker/Bundles/*/src/Spryker/Zed/*/Communication/Controller/', APPLICATION_VENDOR_DIR);
            $this->getConfigHelper()->mockConfigMethod('getControllerDirectories', [$controllerDirectories], static::MODULE_NAME);
            $routerFacade = $this->getBusinessHelper()->getFacade(static::MODULE_NAME);
            $routerPlugin = new ZedRouterPlugin();
            $routerPlugin->setFacade($routerFacade);

            static::$routerPlugin = $routerPlugin;
        }

        return static::$routerPlugin;
    }

    /**
     * @param string $name
     * @param string $path
     * @param callable $controller
     * @param array $defaults
     * @param array $requirements
     * @param array<string, mixed> $options
     * @param string|null $host
     * @param array $schemes
     * @param array $methods
     * @param string|null $condition
     *
     * @return void
     */
    public function addRoute(
        string $name,
        string $path,
        callable $controller,
        array $defaults = [],
        array $requirements = [],
        array $options = [],
        ?string $host = '',
        $schemes = [],
        $methods = [],
        ?string $condition = ''
    ): void {
        $defaults['_controller'] = $controller;
        $route = new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);

        $this->getRouteCollection()->add($name, $route, 0);

        /** @var \Spryker\Zed\Router\Business\Router\ChainRouter $chainRouter */
        $chainRouter = $this->getContainer()->get(static::SERVICE_ROUTER);

        $loader = new ClosureLoader();
        $resource = function () {
            return $this->getRouteCollection();
        };
        $router = new Router($loader, $resource, $this->getConfig(), []);
        $chainRouter->add($router);
    }

    /**
     * @param \Codeception\TestInterface $test
     *
     * @return void
     */
    public function _before(TestInterface $test): void
    {
        parent::_before($test);

        $this->addDependencies();
        $this->getEventDispatcherHelper()->addEventDispatcherPlugin(new RouterListenerEventDispatcherPlugin());

        $this->getApplicationHelper()->addApplicationPlugin(
            $this->getRouterApplicationPluginStub(),
        );
    }

    /**
     * @return void
     */
    protected function addDependencies(): void
    {
        $this->getDependencyProviderHelper()->setDependency(RouterDependencyProvider::ROUTER_PLUGINS, $this->routerPlugins);
    }

    /**
     * @return \Spryker\Zed\Router\Communication\Plugin\Application\RouterApplicationPlugin
     */
    protected function getRouterApplicationPluginStub()
    {
        /** @var \Spryker\Zed\Router\Communication\Plugin\Application\RouterApplicationPlugin $routerApplicationPlugin */
        $routerApplicationPlugin = Stub::make(RouterApplicationPlugin::class, [
            'getFactory' => function () {
                return $this->getFactory();
            },
            'getFacade' => function () {
                return $this->getFacade();
            },
            'getConfig' => function () {
                return $this->getConfig();
            },
        ]);

        return $routerApplicationPlugin;
    }

    /**
     * @return \Spryker\Zed\Router\Communication\RouterCommunicationFactory
     */
    protected function getFactory(): RouterCommunicationFactory
    {
        /** @var \Spryker\Zed\Router\Communication\RouterCommunicationFactory $routerCommunicationFactory */
        $routerCommunicationFactory = $this->getCommunicationHelper()->getFactory(static::MODULE_NAME);

        return $routerCommunicationFactory;
    }

    /**
     * @return \Spryker\Zed\Router\Business\RouterFacadeInterface
     */
    protected function getFacade(): RouterFacadeInterface
    {
        /** @var \Spryker\Zed\Router\Business\RouterFacadeInterface $routerFacade */
        $routerFacade = $this->getBusinessHelper()->getFacade(static::MODULE_NAME);

        return $routerFacade;
    }

    /**
     * @return \Spryker\Zed\Router\RouterConfig
     */
    protected function getConfig(): RouterConfig
    {
        /** @var \Spryker\Zed\Router\RouterConfig $routerConfig */
        $routerConfig = $this->getConfigHelper()->getModuleConfig(static::MODULE_NAME);

        return $routerConfig;
    }

    /**
     * @param \Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface $routerPlugin
     *
     * @return $this
     */
    public function addRouterPlugin(RouterPluginInterface $routerPlugin)
    {
        $this->routerPlugins[] = $routerPlugin;

        $this->addDependencies();

        return $this;
    }

    /**
     * @return \Spryker\Zed\Router\Business\Route\RouteCollection
     */
    protected function getRouteCollection(): RouteCollection
    {
        if ($this->routeCollection === null) {
            $this->routeCollection = new RouteCollection();
        }

        return $this->routeCollection;
    }

    /**
     * @return \Spryker\Service\Container\ContainerInterface
     */
    protected function getContainer(): ContainerInterface
    {
        /** @var \Spryker\Service\Container\ContainerInterface $container */
        $container = $this->getContainerHelper()->getContainer();

        return $container;
    }

    /**
     * @return void
     */
    public function _afterSuite()
    {
        parent::_afterSuite();

        $this->routeCollection = null;
    }
}
