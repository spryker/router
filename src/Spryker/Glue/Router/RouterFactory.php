<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Router;

use Spryker\Glue\Kernel\AbstractFactory;
use Spryker\Glue\Router\Resolver\RequestRequestValueResolver;
use Spryker\Glue\Router\Router\ChainRouter;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\DefaultValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestAttributeValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\RequestValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\SessionValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolver\VariadicValueResolver;
use Symfony\Component\HttpKernel\Controller\ArgumentResolverInterface;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactory;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadataFactoryInterface;

/**
 * @method \Spryker\Glue\Router\RouterConfig getConfig()
 */
class RouterFactory extends AbstractFactory
{
    /**
     * @return \Spryker\Glue\Router\Router\ChainRouter
     */
    public function createRouter()
    {
        return new ChainRouter($this->getRouterPlugins());
    }

    /**
     * @return array<\Spryker\Glue\RouterExtension\Dependency\Plugin\RouterPluginInterface>
     */
    public function getRouterPlugins(): array
    {
        return $this->getProvidedDependency(RouterDependencyProvider::PLUGINS_ROUTER);
    }

    public function createArgumentResolver(): ArgumentResolverInterface
    {
        return new ArgumentResolver(
            $this->createArgumentMetaDataFactory(),
            $this->getArgumentValueResolvers(),
        );
    }

    public function createArgumentMetaDataFactory(): ArgumentMetadataFactoryInterface
    {
        return new ArgumentMetadataFactory();
    }

    /**
     * @return array<\Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface>
     */
    public function getArgumentValueResolvers(): array
    {
        return [
            $this->createRequestAttributeValueResolver(),
            $this->createRequestRequestValueResolver(),
            $this->createRequestValueResolver(),
            $this->createSessionValueResolver(),
            $this->createDefaultValueResolver(),
            $this->createVariadicValueResolver(),
        ];
    }

    public function createRequestAttributeValueResolver(): ArgumentValueResolverInterface
    {
        return new RequestAttributeValueResolver();
    }

    public function createRequestRequestValueResolver(): ArgumentValueResolverInterface
    {
        return new RequestRequestValueResolver();
    }

    public function createRequestValueResolver(): ArgumentValueResolverInterface
    {
        return new RequestValueResolver();
    }

    public function createSessionValueResolver(): ArgumentValueResolverInterface
    {
        return new SessionValueResolver();
    }

    public function createDefaultValueResolver(): ArgumentValueResolverInterface
    {
        return new DefaultValueResolver();
    }

    public function createVariadicValueResolver(): ArgumentValueResolverInterface
    {
        return new VariadicValueResolver();
    }
}
