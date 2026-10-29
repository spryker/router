<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router\Plugin\EventDispatcher;

use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\EventDispatcher\EventDispatcherInterface;
use Spryker\Shared\EventDispatcherExtension\Dependency\Plugin\EventDispatcherPluginInterface;
use Spryker\Yves\Kernel\AbstractPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContextAwareInterface;

/**
 * @method \Spryker\Yves\Router\RouterConfig getConfig()
 * @method \Spryker\Yves\Router\RouterFactory getFactory()
 */
class StoreLocaleRedirectEventDispatcherPlugin extends AbstractPlugin implements EventDispatcherPluginInterface
{
    protected const int EVENT_PRIORITY_KERNEL_REQUEST = 127;

    protected const string SERVICE_LOCALE = 'locale';

    protected const string SERVICE_URL_MATCHER = 'url_matcher';

    protected const string PARAMETER_LANGUAGE = 'language';

    protected const string RESERVED_ROUTE_PATH_PREFIX = '/_';

    /**
     * {@inheritDoc}
     * - Does nothing when store routing is disabled.
     * - Does nothing for a path starting with `/_`, e.g. the Symfony profiler toolbar's `_wdt`/`_profiler` routes.
     * - Does not redirect paths from {@link \Spryker\Yves\Router\RouterConfig::getSslExcludedRouteNames()}, e.g. the health check.
     * - Does not redirect once a store prefix is present, even when the next segment is not a known short language
     *   code (e.g. a content URL with its own embedded locale segment like `/en-us/...`): the content router resolves
     *   such paths on its own, and redirecting would never produce a path matching the stored URL.
     * - Redirects a safe-method request whose path lacks the store prefix to `/{store}/{language}/{path}`.
     * - Takes the store from the Store client's current store and the language from the path or the `locale` service.
     * - Otherwise sets the language on the router context, so URLs generated before the route matches keep it.
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
        $eventDispatcher->addListener(KernelEvents::REQUEST, function (RequestEvent $event) use ($container): void {
            if (!$event->isMainRequest() || !$this->getConfig()->isStoreRoutingEnabled()) {
                return;
            }

            $this->handleRequest($event, $container);
        }, static::EVENT_PRIORITY_KERNEL_REQUEST);

        return $eventDispatcher;
    }

    protected function handleRequest(RequestEvent $event, ContainerInterface $container): void
    {
        $request = $event->getRequest();

        if (str_starts_with($request->getPathInfo(), static::RESERVED_ROUTE_PATH_PREFIX)) {
            return;
        }

        $pathFragments = array_values(array_filter(explode('/', $request->getPathInfo()), static fn (string $pathFragment): bool => $pathFragment !== ''));

        $hasStore = in_array($pathFragments[0] ?? null, $this->getConfig()->getAllowedStores(), true);
        $storeName = $hasStore ? (string)array_shift($pathFragments) : $this->getFactory()->getStoreClient()->getCurrentStore()->getNameOrFail();

        $hasLanguage = in_array($pathFragments[0] ?? null, $this->getConfig()->getAllowedLanguages(), true);
        $language = $hasLanguage ? (string)array_shift($pathFragments) : explode('_', $container->get(static::SERVICE_LOCALE))[0];

        if ($hasStore) {
            $this->getUrlMatcher($container)->getContext()->setParameter(static::PARAMETER_LANGUAGE, $language);

            return;
        }

        if (!$request->isMethodSafe() || in_array($request->getPathInfo(), $this->getConfig()->getSslExcludedRouteNames(), true)) {
            return;
        }

        $event->setResponse(new RedirectResponse($this->buildRedirectUrl($request, $storeName, $language, $pathFragments)));
    }

    /**
     * @param array<string> $pathFragments
     */
    protected function buildRedirectUrl(Request $request, string $storeName, string $language, array $pathFragments): string
    {
        $url = sprintf('%s/%s', $request->getBaseUrl(), implode('/', array_merge([$storeName, $language], $pathFragments)));
        $queryString = $request->getQueryString();

        return $queryString === null ? $url : sprintf('%s?%s', $url, $queryString);
    }

    protected function getUrlMatcher(ContainerInterface $container): RequestContextAwareInterface
    {
        return $container->get(static::SERVICE_URL_MATCHER);
    }
}
