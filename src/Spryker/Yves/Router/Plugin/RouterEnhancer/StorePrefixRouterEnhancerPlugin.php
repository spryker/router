<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router\Plugin\RouterEnhancer;

use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Yves\Router\Router\Router;
use Symfony\Component\Routing\RequestContext;

/**
 * @method \Spryker\Yves\Router\RouterConfig getConfig()
 * @method \Spryker\Yves\Router\RouterFactory getFactory()
 */
class StorePrefixRouterEnhancerPlugin extends AbstractRouterEnhancerPlugin
{
    /**
     * @var string
     */
    protected const PARAMETER_STORE = 'store';

    /**
     * @var string|null
     */
    protected $currentStore;

    public function beforeMatch(string $pathinfo, RequestContext $requestContext): string
    {
        if ($pathinfo === '/') {
            return $pathinfo;
        }

        $pathinfoFragments = explode('/', trim($pathinfo, '/'));
        if (in_array($pathinfoFragments[0], $this->getConfig()->getAllowedStores(), true)) {
            $this->currentStore = array_shift($pathinfoFragments);

            return '/' . implode('/', $pathinfoFragments);
        }

        return $pathinfo;
    }

    public function afterMatch(array $parameters, RequestContext $requestContext): array
    {
        if ($this->currentStore !== null) {
            $parameters[static::PARAMETER_STORE] = $this->currentStore;
        }

        return $parameters;
    }

    public function afterGenerate(string $url, RequestContext $requestContext, int $referenceType): string
    {
        $store = $this->findStore($requestContext);

        if ($store !== null) {
            return $this->buildUrlWithStore($url, $store, $referenceType);
        }

        return $url;
    }

    protected function findStore(RequestContext $requestContext): ?string
    {
        return $requestContext->hasParameter(static::PARAMETER_STORE) && $requestContext->getParameter(static::PARAMETER_STORE) !== null
            ? $requestContext->getParameter(static::PARAMETER_STORE)
            : ($this->getConfig()->isStoreRoutingEnabled()
                ? $this->getFactory()->getStoreClient()->getCurrentStore()->getNameOrFail()
                : null);
    }

    protected function buildUrlWithStore(string $url, string $store, int $referenceType): string
    {
        if ($url === '/') {
            $url = '';
        }

        if ($referenceType === Router::ABSOLUTE_PATH) {
            return sprintf('/%s%s', $store, $url);
        }

        if ($referenceType === Router::ABSOLUTE_URL) {
            $parsedUrl = Url::parse($url);
            $pathWithStore = sprintf('/%s%s', $store, $parsedUrl->getPath());
            $parsedUrl->setPath($pathWithStore);

            return (string)$parsedUrl;
        }

        return $url;
    }
}
