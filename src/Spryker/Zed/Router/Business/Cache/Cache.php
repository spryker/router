<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Business\Cache;

use Spryker\Shared\Router\Cache\CacheInterface;
use Spryker\Zed\Router\Business\Router\ChainRouter;
use Spryker\Zed\Router\RouterConfig;
use Symfony\Component\Filesystem\Filesystem;

class Cache implements CacheInterface
{
    /**
     * @var \Spryker\Zed\Router\Business\Router\ChainRouter
     */
    protected $router;

    /**
     * @var \Spryker\Zed\Router\RouterConfig
     */
    protected $config;

    public function __construct(ChainRouter $router, RouterConfig $config)
    {
        $this->router = $router;
        $this->config = $config;
    }

    public function warmUp(): void
    {
        $this->clear();
        $this->router->warmUp('');
    }

    protected function clear(): void
    {
        $filesystem = new Filesystem();
        $routerConfiguration = $this->config->getRouterConfiguration();

        if (isset($routerConfiguration['cache_dir']) && is_dir($routerConfiguration['cache_dir'])) {
            $filesystem->remove($routerConfiguration['cache_dir']);
        }
    }
}
