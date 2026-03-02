<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Yves\Router\Plugin\RouterEnhancer;

use Spryker\Service\UtilText\Model\Url\Url;
use Spryker\Yves\Router\Router\Router;
use Symfony\Component\Routing\RequestContext;

/**
 * @method \Spryker\Yves\Router\RouterConfig getConfig()
 */
class LanguagePrefixRouterEnhancerPlugin extends AbstractRouterEnhancerPlugin
{
    /**
     * @var string|null
     */
    protected $currentLanguage;

    public function beforeMatch(string $pathinfo, RequestContext $requestContext): string
    {
        if ($pathinfo === '/') {
            return $pathinfo;
        }

        $pathinfoFragments = explode('/', trim($pathinfo, '/'));
        if (in_array($pathinfoFragments[0], $this->getConfig()->getAllowedLanguages(), true)) {
            $this->currentLanguage = array_shift($pathinfoFragments);

            return '/' . implode('/', $pathinfoFragments);
        }

        return $pathinfo;
    }

    public function afterMatch(array $parameters, RequestContext $requestContext): array
    {
        if ($this->currentLanguage !== null) {
            $parameters['language'] = $this->currentLanguage;
        }

        return $parameters;
    }

    public function afterGenerate(string $url, RequestContext $requestContext, int $referenceType): string
    {
        $language = $this->findLanguage($requestContext);

        if ($language !== null) {
            return $this->buildUrlWithLanguage($url, $language, $referenceType);
        }

        return $url;
    }

    protected function findLanguage(RequestContext $requestContext): ?string
    {
        if ($requestContext->hasParameter('language')) {
            return $requestContext->getParameter('language');
        }

        if ($requestContext->hasParameter('_locale')) {
            $locale = $requestContext->getParameter('_locale');
            $language = $this->getLanguageFromLocale($locale);

            return $language;
        }

        return null;
    }

    protected function buildUrlWithLanguage(string $url, string $language, int $referenceType): string
    {
        if ($url === '/') {
            $url = '';
        }

        if ($referenceType === Router::ABSOLUTE_PATH) {
            return sprintf('/%s%s', $language, $url);
        }

        if ($referenceType === Router::ABSOLUTE_URL) {
            $parsedUrl = Url::parse($url);
            $pathWithLanguage = sprintf('/%s%s', $language, $parsedUrl->getPath());
            $parsedUrl->setPath($pathWithLanguage);

            return (string)$parsedUrl;
        }

        return $url;
    }

    protected function getLanguageFromLocale(string $locale): string
    {
        $localeFragments = explode('_', $locale);

        return current($localeFragments);
    }
}
