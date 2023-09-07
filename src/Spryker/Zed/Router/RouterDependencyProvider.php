<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router;

use Spryker\Zed\Kernel\AbstractBundleDependencyProvider;
use Spryker\Zed\Kernel\Container;

/**
 * @method \Spryker\Zed\Router\RouterConfig getConfig()
 */
class RouterDependencyProvider extends AbstractBundleDependencyProvider
{
    /**
     * @var string
     */
    public const ROUTER_PLUGINS = 'ROUTER_PLUGINS';

    /**
     * @var string
     */
    public const PLUGINS_BACKOFFICE_ROUTER = 'PLUGINS_BACKOFFICE_ROUTER';

    /**
     * @var string
     */
    public const PLUGINS_BACKEND_GATEWAY_ROUTER = 'PLUGINS_BACKEND_GATEWAY_ROUTER';

    /**
     * @var string
     */
    public const PLUGINS_BACKEND_API_ROUTER = 'PLUGINS_BACKEND_API_ROUTER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_PORTAL_ROUTER = 'PLUGINS_MERCHANT_PORTAL_ROUTER';

    /**
     * @var string
     */
    public const ROUTER_ENHANCER_PLUGINS = 'router enhancer plugin';

    /**
     * @var string
     */
    public const PLUGINS_BACKOFFICE_ROUTER_ENHANCER = 'PLUGINS_BACKOFFICE_ROUTER_ENHANCER';

    /**
     * @var string
     */
    public const PLUGINS_MERCHANT_PORTAL_ROUTER_ENHANCER = 'PLUGINS_MERCHANT_PORTAL_ROUTER_ENHANCER';

    /**
     * @var string
     */
    public const PLUGINS_BACKEND_GATEWAY_ROUTER_ENHANCER = 'PLUGINS_BACKEND_GATEWAY_ROUTER_ENHANCER';

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    public function provideBusinessLayerDependencies(Container $container): Container
    {
        $container = $this->addRouterPlugins($container);
        $container = $this->addRouterEnhancerPlugins($container);

        $container = $this->addBackofficeRouterPlugins($container);
        $container = $this->addBackofficeRouterEnhancerPlugins($container);

        $container = $this->addBackendGatewayRouterPlugins($container);
        $container = $this->addBackendGatewayRouterEnhancerPlugins($container);

        $container = $this->addBackendApiRouterPlugins($container);

        $container = $this->addMerchantPortalRouterPlugins($container);
        $container = $this->addMerchantPortalRouterEnhancerPlugins($container);

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addBackofficeRouterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_BACKOFFICE_ROUTER, function () {
            return $this->getBackofficeRouterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getBackofficeRouterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addBackofficeRouterEnhancerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_BACKOFFICE_ROUTER_ENHANCER, function () {
            return $this->getBackofficeRouterEnhancerPlugins();
        });

        return $container;
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantPortalRouterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_PORTAL_ROUTER, function () {
            return $this->getMerchantPortalRouterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getMerchantPortalRouterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addMerchantPortalRouterEnhancerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_MERCHANT_PORTAL_ROUTER_ENHANCER, function () {
            return $this->getMerchantPortalRouterEnhancerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface>
     */
    protected function getMerchantPortalRouterEnhancerPlugins(): array
    {
        return [];
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface>
     */
    protected function getBackofficeRouterEnhancerPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addBackendGatewayRouterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_BACKEND_GATEWAY_ROUTER, function () {
            return $this->getBackendGatewayRouterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getBackendGatewayRouterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addBackendGatewayRouterEnhancerPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_BACKEND_GATEWAY_ROUTER_ENHANCER, function () {
            return $this->getBackendGatewayRouterEnhancerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface>
     */
    protected function getBackendGatewayRouterEnhancerPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addBackendApiRouterPlugins(Container $container): Container
    {
        $container->set(static::PLUGINS_BACKEND_API_ROUTER, function () {
            return $this->getBackendApiRouterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getBackendApiRouterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addRouterPlugins(Container $container): Container
    {
        $container->set(static::ROUTER_PLUGINS, function () {
            return $this->getRouterPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    protected function getRouterPlugins(): array
    {
        return [];
    }

    /**
     * @param \Spryker\Zed\Kernel\Container $container
     *
     * @return \Spryker\Zed\Kernel\Container
     */
    protected function addRouterEnhancerPlugins(Container $container): Container
    {
        $container->set(static::ROUTER_ENHANCER_PLUGINS, function () {
            return $this->getRouterEnhancerPlugins();
        });

        return $container;
    }

    /**
     * @return array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface>
     */
    protected function getRouterEnhancerPlugins(): array
    {
        return [];
    }
}
