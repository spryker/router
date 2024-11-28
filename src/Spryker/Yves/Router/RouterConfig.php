<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router;

use Spryker\Shared\Router\RouterConstants;
use Spryker\Yves\Kernel\AbstractBundleConfig;
use Spryker\Yves\Router\Generator\UrlGenerator;
use Spryker\Yves\Router\UrlMatcher\CompiledUrlMatcher;

/**
 * @method \Spryker\Shared\Router\RouterConfig getSharedConfig()
 */
class RouterConfig extends AbstractBundleConfig
{
    /**
     * Specification:
     * - Returns a Router configuration which makes use of a Router cache.
     *
     * @api
     *
     * @see \Symfony\Component\Routing\Router::setOptions()
     *
     * @return array<string, mixed>
     */
    public function getRouterConfiguration(): array
    {
        return [
            'cache_dir' => $this->getCachePathIfCacheEnabled(),
            'generator_class' => UrlGenerator::class,
            'matcher_class' => CompiledUrlMatcher::class,
        ];
    }

    /**
     * Specification:
     * - Returns a Router configuration which does not make use of a Router cache.
     * - Fallback for development which is executed when the cached Router is not able to match.
     *
     * @api
     *
     * @see \Symfony\Component\Routing\Router::setOptions()
     *
     * @return array<string, mixed>
     */
    public function getDevelopmentRouterConfiguration(): array
    {
        $routerConfiguration = $this->getRouterConfiguration();
        $routerConfiguration['cache_dir'] = null;

        return $routerConfiguration;
    }

    /**
     * @return string|null
     */
    protected function getCachePathIfCacheEnabled(): ?string
    {
        if ($this->get(RouterConstants::YVES_IS_CACHE_ENABLED, true)) {
            return $this->get(RouterConstants::YVES_CACHE_PATH, $this->getSharedConfig()->getDefaultRouterCachePath());
        }

        return null;
    }

    /**
     * Specification:
     * - Returns if the SSl is enabled.
     * - When it is enabled and the current request is not secure, the Router will redirect to a secured URL.
     *
     * @api
     *
     * @return bool
     */
    public function isSslEnabled(): bool
    {
        return $this->get(RouterConstants::YVES_IS_SSL_ENABLED, true);
    }

    /**
     * Specification:
     * - Returns SSl excluded Route names.
     * - When SSL is enabled and the current Route name is excluded, the Router will not redirect to a secured URL.
     *
     * @api
     *
     * @return array<string>
     */
    public function getSslExcludedRouteNames(): array
    {
        return $this->get(RouterConstants::YVES_SSL_EXCLUDED_ROUTE_NAMES, []);
    }

    /**
     * Specification:
     * - Returns a list of supported languages for Route manipulation.
     * - Will be used to strip of language information from a route before a route is matched.
     *
     * @example Incoming URL `/en/home` will be manipulated to `/home` because the router only knows URL's without any optional pre/suffix.
     *
     * @api
     *
     * @see \Spryker\Yves\Router\Plugin\RouterEnhancer\LanguagePrefixRouterEnhancerPlugin
     *
     * @return array<string>
     */
    public function getAllowedLanguages(): array
    {
        return [
            'de',
            'en',
        ];
    }

    /**
     * Specification:
     * - Returns a list of supported stores for Route manipulation.
     * - Will be used to strip of store information from a route before a route is matched.
     *
     * @api
     *
     * @example Incoming URL `/DE/home` will be manipulated to `/home` because the router only knows URL's without any optional pre/suffix.
     *
     * @see \Spryker\Yves\Router\Plugin\RouterEnhancer\StorePrefixRouterEnhancerPlugin
     *
     * @return array<string>
     */
    public function getAllowedStores(): array
    {
        return [
            'DE',
            'US',
        ];
    }

    /**
     * Specification:
     * - Returns true if the store routing is enabled.
     *
     * @api
     *
     * @return bool
     */
    public function isStoreRoutingEnabled(): bool
    {
        return $this->get(RouterConstants::IS_STORE_ROUTING_ENABLED, false);
    }
}
