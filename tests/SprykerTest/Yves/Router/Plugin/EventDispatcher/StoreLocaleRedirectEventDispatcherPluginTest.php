<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Router\Plugin\EventDispatcher;

use Codeception\Test\Unit;
use Generated\Shared\Transfer\StoreTransfer;
use Spryker\Shared\EventDispatcher\EventDispatcher;
use Spryker\Shared\Router\RouterConstants;
use Spryker\Yves\Router\Dependency\Client\RouterToStoreClientInterface;
use Spryker\Yves\Router\Plugin\EventDispatcher\StoreLocaleRedirectEventDispatcherPlugin;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RequestContext;
use Symfony\Component\Routing\RequestContextAwareInterface;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Yves
 * @group Router
 * @group Plugin
 * @group EventDispatcher
 * @group StoreLocaleRedirectEventDispatcherPluginTest
 * Add your own group annotations below this line
 */
class StoreLocaleRedirectEventDispatcherPluginTest extends Unit
{
    protected const string CURRENT_STORE_NAME = 'DE';

    /**
     * @var \SprykerTest\Yves\Router\RouterYvesTester
     */
    protected $tester;

    public function setUp(): void
    {
        parent::setUp();

        $this->tester->mockEnvironmentConfig(RouterConstants::IS_STORE_ROUTING_ENABLED, true);
        $this->tester->mockFactoryMethod('getStoreClient', $this->createStoreClientMock());
    }

    public function testRedirectsToStoreAndLanguagePrefixedUrlWhenBothAreMissing(): void
    {
        $response = $this->dispatchRequest('/foo/bar');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/DE/en/foo/bar', $response->getTargetUrl());
    }

    public function testRedirectsRootPathToStoreAndLanguagePrefixedUrl(): void
    {
        $response = $this->dispatchRequest('/');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/DE/en', $response->getTargetUrl());
    }

    public function testDoesNotRedirectWhenStoreAndLanguagePrefixAreAlreadyPresent(): void
    {
        $response = $this->dispatchRequest('/DE/en/foo');

        $this->assertNull($response);
    }

    public function testDoesNotRedirectWhenStorePrefixIsPresentButNextSegmentIsNotAKnownLanguageCode(): void
    {
        $response = $this->dispatchRequest('/DE/en-us/product-6ae1fcf806dff965358517-232');

        $this->assertNull($response);
    }

    public function testDoesNotRedirectWhenStoreRoutingIsDisabled(): void
    {
        $this->tester->mockEnvironmentConfig(RouterConstants::IS_STORE_ROUTING_ENABLED, false);

        $response = $this->dispatchRequest('/foo');

        $this->assertNull($response);
    }

    public function testDoesNotRedirectNonSafeMethodRequests(): void
    {
        $response = $this->dispatchRequest('/foo', Request::METHOD_POST);

        $this->assertNull($response);
    }

    public function testDoesNotRedirectReservedRoutePaths(): void
    {
        $response = $this->dispatchRequest('/_profiler/wdt/a5dc8e');

        $this->assertNull($response);
    }

    protected function dispatchRequest(string $pathInfo, string $method = Request::METHOD_GET): ?Response
    {
        $plugin = new StoreLocaleRedirectEventDispatcherPlugin();
        $plugin->setFactory($this->tester->getFactory());

        $container = $this->tester->getContainer();
        $container->set('locale', 'en_US');
        $container->set('url_matcher', $this->createUrlMatcherStub());

        $eventDispatcher = $plugin->extend(new EventDispatcher(), $container);

        $request = Request::create($pathInfo, $method);
        $event = new RequestEvent($this->createKernelMock(), $request, HttpKernelInterface::MAIN_REQUEST);

        $eventDispatcher->dispatch($event, KernelEvents::REQUEST);

        return $event->getResponse();
    }

    protected function createStoreClientMock(): RouterToStoreClientInterface
    {
        $storeClientMock = $this->createMock(RouterToStoreClientInterface::class);
        $storeClientMock->method('getCurrentStore')->willReturn((new StoreTransfer())->setName(static::CURRENT_STORE_NAME));

        return $storeClientMock;
    }

    protected function createUrlMatcherStub(): RequestContextAwareInterface
    {
        return new class implements RequestContextAwareInterface {
            protected RequestContext $context;

            public function __construct()
            {
                $this->context = new RequestContext();
            }

            public function setContext(RequestContext $context): void
            {
                $this->context = $context;
            }

            public function getContext(): RequestContext
            {
                return $this->context;
            }
        };
    }

    protected function createKernelMock(): HttpKernelInterface
    {
        return $this->createMock(HttpKernelInterface::class);
    }
}
