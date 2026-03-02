<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Business\Reader;

use Generated\Shared\Transfer\RouterActionCollectionTransfer;
use Generated\Shared\Transfer\RouterBundleCollectionTransfer;
use Generated\Shared\Transfer\RouterControllerCollectionTransfer;

interface RouterReaderInterface
{
    public function getBundleCollection(): RouterBundleCollectionTransfer;

    public function getControllerCollection(string $bundle): RouterControllerCollectionTransfer;

    public function getActionCollection(string $bundle, string $controller): RouterActionCollectionTransfer;
}
