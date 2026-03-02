<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Business\Router\RouterResource;

use Symfony\Component\Finder\Finder;

class BackendGatewayRouterResource extends AbstractRouterResource
{
    protected function getFinder(): Finder
    {
        $finder = new Finder();
        $finder->files()
            ->in($this->config->getControllerDirectories())
            ->name('GatewayController.php');

        return $finder;
    }
}
