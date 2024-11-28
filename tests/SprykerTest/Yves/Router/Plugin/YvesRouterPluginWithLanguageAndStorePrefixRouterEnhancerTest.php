<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Router\Plugin;

use Codeception\Test\Unit;
use Spryker\Shared\Router\RouterConstants;
use Spryker\Yves\Router\Plugin\RouteManipulator\LanguageDefaultPostAddRouteManipulatorPlugin;
use Spryker\Yves\Router\Plugin\RouteManipulator\StoreDefaultPostAddRouteManipulatorPlugin;
use Spryker\Yves\Router\Plugin\Router\YvesRouterPlugin;
use Spryker\Yves\Router\Plugin\RouterEnhancer\LanguagePrefixRouterEnhancerPlugin;
use Spryker\Yves\Router\Plugin\RouterEnhancer\StorePrefixRouterEnhancerPlugin;
use SprykerTest\Yves\Router\Plugin\Fixtures\RouteProviderPlugin;
use Symfony\Component\Routing\RequestContext;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Yves
 * @group Router
 * @group Plugin
 * @group YvesRouterPluginWithLanguageAndStorePrefixRouterEnhancerTest
 * Add your own group annotations below this line
 */
class YvesRouterPluginWithLanguageAndStorePrefixRouterEnhancerTest extends Unit
{
    /**
     * @var \SprykerTest\Yves\Router\RouterYvesTester
     */
    protected $tester;

    /**
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->tester->setupStorageRedisConfig();
        $this->tester->mockEnvironmentConfig(RouterConstants::YVES_IS_CACHE_ENABLED, false);

        $this->tester->mockFactoryMethod('getRouteProviderPlugins', [
            new RouteProviderPlugin(),
        ]);

        $this->tester->mockFactoryMethod('getRouteManipulatorPlugins', [
            new LanguageDefaultPostAddRouteManipulatorPlugin(),
            new StoreDefaultPostAddRouteManipulatorPlugin(),
        ]);

        $this->tester->mockFactoryMethod('getRouterEnhancerPlugins', [
            new LanguagePrefixRouterEnhancerPlugin(),
            new StorePrefixRouterEnhancerPlugin(),
        ]);
    }

    /**
     * @dataProvider matcherDataProvider
     *
     * @param string $url
     * @param string $routeName
     * @param string $language
     * @param string $store
     *
     * @return void
     */
    public function testMatchReturnsParameterWithLanguageAndStore(string $url, string $routeName, string $language, string $store): void
    {
        $routerPlugin = new YvesRouterPlugin();
        $routerPlugin->setFactory($this->tester->getFactory());

        $router = $routerPlugin->getRouter();

        $parameters = $router->match($url);

        $this->assertSame($routeName, $parameters['_route']);
        $this->assertSame($language, $parameters['language']);
        $this->assertSame($store, $parameters['store']);
    }

    /**
     * @dataProvider generatorDataProvider
     *
     * @param string $url
     * @param string $routeName
     * @param string $language
     * @param string $store
     *
     * @return void
     */
    public function testGenerateReturnsUrlWithLanguageAndStoreWhenLanguageAndStoreAreInContext(
        string $url,
        string $routeName,
        string $language,
        string $store
    ): void {
        $routerPlugin = new YvesRouterPlugin();
        $routerPlugin->setFactory($this->tester->getFactory());

        $requestContext = new RequestContext();
        $requestContext->setParameter('language', $language);
        $requestContext->setParameter('store', $store);

        $router = $routerPlugin->getRouter();
        $router->setContext($requestContext);

        $generatedUrl = $router->generate($routeName);

        $this->assertSame($url, $generatedUrl);
    }

    /**
     * @dataProvider generatorWithoutLanguageAndStoreDataProvider
     *
     * @param string $url
     * @param string $routeName
     *
     * @return void
     */
    public function testGenerateReturnsUrlWithoutLanguageAndStoreWhenLanguageAndStoreAreNotInContext(string $url, string $routeName): void
    {
        $this->tester->mockEnvironmentConfig(RouterConstants::IS_STORE_ROUTING_ENABLED, false);
        $routerPlugin = new YvesRouterPlugin();
        $routerPlugin->setFactory($this->tester->getFactory());

        $router = $routerPlugin->getRouter();

        $generatedUrl = $router->generate($routeName);

        $this->assertSame($url, $generatedUrl);
    }

    /**
     * @dataProvider generatorWithoutLanguageAndStoreDataProviderWithStoreRoutingEnabled
     *
     * @param string $url
     * @param string $routeName
     *
     * @return void
     */
    public function testGenerateReturnsUrlWithoutLanguageAndStoreWhenLanguageAndStoreAreNotInContextWithStoreRoutingEnabled(
        string $url,
        string $routeName
    ): void {
        $this->tester->mockEnvironmentConfig(RouterConstants::IS_STORE_ROUTING_ENABLED, true);
        $routerPlugin = new YvesRouterPlugin();
        $routerPlugin->setFactory($this->tester->getFactory());
        $router = $routerPlugin->getRouter();

        $generatedUrl = $router->generate($routeName);

        $this->assertSame($url, $generatedUrl);
    }

    /**
     * @return array<array<string>>
     */
    public function matcherDataProvider(): array
    {
        return [
            ['/', 'home', 'en', 'US'],
            ['/de/DE', 'home', 'de', 'DE'],
            ['/de/DE/foo', 'foo', 'de', 'DE'],
            ['/foo', 'foo', 'en', 'US'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function generatorDataProvider(): array
    {
        return [
            ['/en/US', 'home', 'en', 'US'],
            ['/de/DE', 'home', 'de', 'DE'],
            ['/de/DE/foo', 'foo', 'de', 'DE'],
            ['/de/DE/foo', 'foo', 'de', 'DE'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function generatorWithoutLanguageAndStoreDataProvider(): array
    {
        return [
            ['/', 'home'],
            ['/foo', 'foo'],
        ];
    }

    /**
     * @return array<array<string>>
     */
    public function generatorWithoutLanguageAndStoreDataProviderWithStoreRoutingEnabled(): array
    {
        return [
            ['/DE', 'home'],
            ['/DE/foo', 'foo'],
        ];
    }
}
