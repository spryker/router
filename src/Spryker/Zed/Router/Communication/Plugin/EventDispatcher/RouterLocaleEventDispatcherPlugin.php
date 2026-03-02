<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Zed\Router\Communication\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Zed\Kernel\Communication\AbstractPlugin;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContextAwareInterface;

/**
 * @method \Spryker\Zed\Router\RouterConfig getConfig()
 * @method \Spryker\Zed\Router\Communication\RouterCommunicationFactory getFactory()
 * @method \Spryker\Zed\Router\Business\RouterFacadeInterface getFacade()
 */
class RouterLocaleEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    /**
     * @see \Spryker\Shared\Application\Application::SERVICE_ROUTER
     *
     * @var string
     */
    protected const SERVICE_ROUTER = 'routers';

    /**
     * @var int
     */
    protected const EVENT_PRIORITY_KERNEL_REQUEST = 16;

    /**
     * @var int
     */
    protected const EVENT_PRIORITY_KERNEL_FINISH_REQUEST = 0;

    /**
     * @var string
     */
    protected const SERVICE_URL_MATCHER = 'url_matcher';

    /**
     * @var string
     */
    protected const SERVICE_REQUEST_STACK = 'request_stack';

    /**
     * {@inheritDoc}
     * - Adds event listener that set the locale to the router context.
     *
     * @api
     *
     * @param \Spryker\Shared\EventDispatcher\EventDispatcherInterface $eventDispatcher
     * @param \Spryker\Service\Container\ContainerInterface $container
     *
     * @return \Spryker\Shared\EventDispatcher\EventDispatcherInterface
     */
    public function extend(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher = $this->addListeners($eventDispatcher, $container);

        return $eventDispatcher;
    }

    protected function addListeners(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher = $this->addRequestKernelEventListener($eventDispatcher, $container);
        $eventDispatcher = $this->addFinishRequestKernelEventListener($eventDispatcher, $container);

        return $eventDispatcher;
    }

    protected function addRequestKernelEventListener(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addListener(
            KernelEvents::REQUEST,
            function (RequestEvent $event) use ($container): void {
                $request = $event->getRequest();
                $this->setRouterContext($request, $this->getUrlMatcher($container));
            },
            static::EVENT_PRIORITY_KERNEL_REQUEST,
        );

        return $eventDispatcher;
    }

    protected function addFinishRequestKernelEventListener(EventDispatcherInterface $eventDispatcher, ContainerInterface $container): EventDispatcherInterface
    {
        $eventDispatcher->addListener(
            KernelEvents::FINISH_REQUEST,
            function (FinishRequestEvent $event) use ($container): void {
                $requestStack = $this->getRequestStack($container);
                $parentRequest = $requestStack->getParentRequest();
                if ($parentRequest !== null) {
                    $this->setRouterContext($parentRequest, $this->getUrlMatcher($container));
                }
            },
            static::EVENT_PRIORITY_KERNEL_FINISH_REQUEST,
        );

        return $eventDispatcher;
    }

    protected function setRouterContext(Request $request, RequestContextAwareInterface $router): RequestContextAwareInterface
    {
        $router->getContext()->setParameter('_locale', $request->getLocale());

        return $router;
    }

    protected function getUrlMatcher(ContainerInterface $container): RequestContextAwareInterface
    {
        return $container->get(static::SERVICE_ROUTER);
    }

    protected function getRequestStack(ContainerInterface $container): RequestStack
    {
        return $container->get(static::SERVICE_REQUEST_STACK);
    }
}
