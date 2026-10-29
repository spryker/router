<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Yves\Router\Plugin\RouterEnhancer;

use Codeception\Test\Unit;
use Spryker\Yves\Router\Plugin\RouterEnhancer\LanguagePrefixRouterEnhancerPlugin;
use Spryker\Yves\Router\Router\Router;
use Symfony\Component\Routing\RequestContext;

/**
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Yves
 * @group Router
 * @group Plugin
 * @group RouterEnhancer
 * @group LanguagePrefixRouterEnhancerPluginTest
 * Add your own group annotations below this line
 */
class LanguagePrefixRouterEnhancerPluginTest extends Unit
{
    public function testAfterGeneratePrefersLocaleParameterOverLanguageParameterWhenBothAreSet(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setParameter('language', 'en');
        $requestContext->setParameter('_locale', 'de_DE');

        $plugin = new LanguagePrefixRouterEnhancerPlugin();

        $url = $plugin->afterGenerate('/foo', $requestContext, Router::ABSOLUTE_PATH);

        $this->assertSame('/de/foo', $url);
    }

    public function testAfterGenerateFallsBackToLanguageParameterWhenLocaleIsNotSet(): void
    {
        $requestContext = new RequestContext();
        $requestContext->setParameter('language', 'en');

        $plugin = new LanguagePrefixRouterEnhancerPlugin();

        $url = $plugin->afterGenerate('/foo', $requestContext, Router::ABSOLUTE_PATH);

        $this->assertSame('/en/foo', $url);
    }
}
