<?php
// phpcs:ignoreFile

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Communication\Controller;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Zed\Kernel\Communication\Controller\AbstractController;

/**
 * @method \Spryker\Zed\Router\Communication\RouterCommunicationFactory getFactory()
 * @method \Spryker\Zed\Router\Business\RouterFacadeInterface getFacade()
 */
class InitializableTestController extends AbstractController
{
    /**
     * @var array<string, bool>
     */
    public static $calledMethods = [];

    /**
     * @param \Silex\Application|\Spryker\Service\Container\ContainerInterface $application
     *
     * @return $this
     */
    public function setApplication($application)
    {
        parent::setApplication($application);

        static::$calledMethods['setApplication'] = true;

        return $this;
    }

    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        static::$calledMethods['initialize'] = true;
    }

    /**
     * @return void
     */
    public function mockAction(): void
    {
    }

    /**
     * @return string
     */
    public function __invoke(): string
    {
        return 'Controller';
    }

    /**
     * @return void
     */
    public static function resetCalledMethods(): void
    {
        static::$calledMethods = [];
    }
}
