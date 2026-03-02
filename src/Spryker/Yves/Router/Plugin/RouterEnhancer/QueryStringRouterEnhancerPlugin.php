<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router\Plugin\RouterEnhancer;

use Symfony\Component\Routing\RequestContext;

/**
 * @method \Spryker\Yves\Router\RouterConfig getConfig()
 */
class QueryStringRouterEnhancerPlugin extends AbstractRouterEnhancerPlugin
{
    public function afterGenerate(string $url, RequestContext $requestContext, int $referenceType): string
    {
        $queryParams = $this->getQueryString($requestContext);

        if ($queryParams) {
            return sprintf('%s%s', $url, $queryParams);
        }

        return $url;
    }

    protected function getQueryString(RequestContext $requestContext): ?string
    {
        return $requestContext->getQueryString();
    }
}
