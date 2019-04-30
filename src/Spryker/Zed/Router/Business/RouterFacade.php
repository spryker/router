<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Business;

use Spryker\Zed\Kernel\Business\AbstractFacade;
use Spryker\Zed\Router\Business\Router\ChainRouter;
use Spryker\Zed\Router\Business\Router\RouterInterface;

/**
 * @method \Spryker\Zed\Router\Business\RouterBusinessFactory getFactory()
 */
class RouterFacade extends AbstractFacade implements RouterFacadeInterface
{
    /**
     * Specification:
     * - Returns a ChainRouter which is added to the Application.
     * - Uses RouterExtensionPluginInterfaces to add Router to the ChainRouter.
     *
     * @api
     *
     * @return \Spryker\Zed\Router\Business\Router\ChainRouter
     */
    public function getRouter(): ChainRouter
    {
        return $this->getFactory()->createRouter();
    }

    /**
     * Specification:
     * - Returns Router which handles Zed routes.
     *
     * @api
     *
     * @return \Spryker\Zed\Router\Business\Router\RouterInterface
     */
    public function getZedRouter(): RouterInterface
    {
        return $this->getFactory()->createZedRouter();
    }
}
