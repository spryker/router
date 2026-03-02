<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Communication\Plugin\Console;

use Spryker\Zed\Kernel\Communication\Console\Console;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @method \Spryker\Zed\Router\Business\RouterFacadeInterface getFacade()
 * @method \Spryker\Zed\Router\Communication\RouterCommunicationFactory getFactory()
 */
class BackofficeRouterCacheWarmUpConsole extends Console
{
    /**
     * @var string
     */
    protected const NAME = 'router:cache:warm-up:backoffice';

    protected function configure(): void
    {
        $this
            ->setName(static::NAME)
            ->setDescription('Builds a fresh cache for the Backoffice router.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->getFacade()->warmUpBackofficeRouterCache();

        return static::CODE_SUCCESS;
    }
}
