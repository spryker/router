<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Shared\Router\Resolver;

use Closure;
use InvalidArgumentException;
use Spryker\Service\Container\ContainerDelegator;
use Spryker\Service\Container\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

class ControllerResolver implements ControllerResolverInterface
{
    /**
     * @var \Spryker\Service\Container\ContainerInterface
     */
    protected $container;

    /**
     * @param \Spryker\Service\Container\ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return callable|false
     */
    public function getController(Request $request): callable|false
    {
        $controller = $request->attributes->get('_controller');

        if (!$controller) {
            return false;
        }

        if (is_string($controller)) {
            return $this->getControllerFromString($request, $controller);
        }

        if (is_array($controller)) {
            return $this->getControllerFromArray($request, $controller);
        }

        if (is_object($controller)) {
            return $this->getControllerFromObject($request, $controller);
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $controller
     *
     * @return callable|false
     */
    protected function getControllerFromString(Request $request, string $controller)
    {
        if (strpos($controller, ':') === false && strpos($controller, '.') === false) {
            return false;
        }

        $globalContainer = ContainerDelegator::getInstance();

        // Check for Symfony Bundle Controller
        if ($globalContainer->has($controller)) {
            /** @phpstan-var object $controllerInstance */
            $controllerInstance = $globalContainer->get($controller);
            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return $controllerInstance;
        }

        [$controllerServiceIdentifier, $actionName] = explode(':', $controller);

        if ($this->container->has($controllerServiceIdentifier)) {
            $controllerClassName = $this->container->get($controllerServiceIdentifier);
            $controllerInstance = new $controllerClassName();
            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return [$controllerInstance, $actionName];
        }

        if (!$this->container->has('container')) {
            return false;
        }

        $container = $this->container->get('container');

        if ($container->has($controllerServiceIdentifier)) {
            $controllerClassName = $container->get($controllerServiceIdentifier);

            $controllerInstance = $controllerClassName;

            if (is_string($controllerClassName)) {
                $controllerInstance = new $controllerClassName();
            }

            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return [$controllerInstance, $actionName];
        }

        return false;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param array $controller
     *
     * @return callable
     */
    protected function getControllerFromArray(Request $request, array $controller)
    {
        /** @var \Spryker\Service\Container\ContainerDelegator|null $container */
        $container = $this->container->has(ContainerDelegator::class) ? $this->container->get(ContainerDelegator::class) : null;

        if ($controller[0] instanceof Closure) {
            $controllerInstance = $controller[0]();
            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return [$controllerInstance, $controller[1]];
        }

        if ($container && is_string($controller[0]) && $container->has($controller[0])) {
            /** @phpstan-var object $controllerInstance */
            $controllerInstance = $container->get($controller[0]);
            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return [$controllerInstance, $controller[1]];
        }

        if (is_callable($controller[0])) {
            $controllerInstance = $controller[0]();
            $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

            /** @phpstan-var callable */
            return [$controllerInstance, $controller[1]];
        }

        $controllerInstance = new $controller[0]();
        $controllerInstance = $this->injectContainerAndInitialize($controllerInstance);

        /** @phpstan-var callable */
        return [$controllerInstance, $controller[1]];
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param object $controller
     *
     * @throws \InvalidArgumentException
     *
     * @return callable
     */
    protected function getControllerFromObject(Request $request, $controller)
    {
        if (method_exists($controller, '__invoke')) {
            /** @phpstan-var callable $controller */
            $controller = $this->injectContainerAndInitialize($controller);

            return $controller;
        }

        throw new InvalidArgumentException(sprintf('Controller "%s" for URI "%s" is not callable.', get_class($controller), $request->getPathInfo()));
    }

    /**
     * @param object $controller
     *
     * @return object
     */
    protected function injectContainerAndInitialize($controller)
    {
        if (method_exists($controller, 'setApplication')) {
            $controller->setApplication($this->container);
        }

        if (method_exists($controller, 'initialize')) {
            $controller->initialize();
        }

        return $controller;
    }

    /**
     * @deprecated This method is deprecated as of 3.1 and will be removed in 4.0. Implement the ArgumentResolverInterface and inject it in the HttpKernel instead.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Spryker\Zed\Kernel\Communication\Controller\AbstractController $controller
     *
     * @return array
     */
    public function getArguments(Request $request, $controller)
    {
        return [];
    }
}
