<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Business\Generator;

use Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerAwareInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\CompiledUrlGenerator;
use Symfony\Component\Routing\Router as SymfonyRouter;

class UrlGenerator extends CompiledUrlGenerator implements RouterEnhancerAwareInterface
{
    /**
     * @var \Symfony\Component\HttpFoundation\Request|null
     */
    protected $request;

    /**
     * @var array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface>
     */
    protected $routerEnhancerPlugins;

    /**
     * @param array<\Spryker\Zed\RouterExtension\Dependency\Plugin\RouterEnhancerPluginInterface> $routerEnhancerPlugins
     *
     * @return void
     */
    public function setRouterEnhancerPlugins(array $routerEnhancerPlugins): void
    {
        $this->routerEnhancerPlugins = $routerEnhancerPlugins;
    }

    public function generate(string $name, array $parameters = [], int $referenceType = SymfonyRouter::ABSOLUTE_PATH): string
    {
        $generatedUrl = parent::generate($name, $parameters, $referenceType);

        foreach (array_reverse($this->routerEnhancerPlugins) as $routerEnhancerPlugin) {
            $generatedUrl = $routerEnhancerPlugin->afterGenerate($generatedUrl, $this->getContext(), $referenceType);
        }

        return $generatedUrl;
    }

    protected function getRequest(): Request
    {
        if ($this->request === null) {
            $this->request = Request::createFromGlobals();
        }

        return $this->request;
    }
}
