<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Router\Plugin\Router;

use Spryker\Glue\Kernel\AbstractPlugin;
use Spryker\Glue\RouterExtension\Dependency\Plugin\RouterPluginInterface;
use Symfony\Component\Routing\RouterInterface;

class SymfonyFrameworkRouterPlugin extends AbstractPlugin implements RouterPluginInterface
{
    /**
     * @return \Symfony\Component\Routing\RouterInterface
     */
    public function getRouter(): RouterInterface
    {
        /** @var \Symfony\Component\Routing\RouterInterface */
        return $this->getService('router');
    }
}
