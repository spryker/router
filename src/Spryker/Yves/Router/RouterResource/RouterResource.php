<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router\RouterResource;

use Closure;
use Spryker\Yves\Router\Route\RouteCollection;

class RouterResource implements ResourceInterface
{
    protected RouteCollection $routeCollection;

    protected Closure $routeProviderLoader;

    public function __construct(RouteCollection $routeCollection, Closure $routeProviderLoader)
    {
        $this->routeCollection = $routeCollection;
        $this->routeProviderLoader = $routeProviderLoader;
    }

    public function __invoke(): RouteCollection
    {
        /** @var array<\Spryker\Yves\RouterExtension\Dependency\Plugin\RouteProviderPluginInterface> $routeProviderPlugins */
        $routeProviderPlugins = ($this->routeProviderLoader)();

        foreach ($routeProviderPlugins as $routeProviderPlugin) {
            $this->routeCollection = $routeProviderPlugin->addRoutes($this->routeCollection);
        }

        return $this->routeCollection;
    }
}
