<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace Spryker\Glue\Router\Resolver;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

class RequestRequestValueResolver implements ArgumentValueResolverInterface
{
    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument
     *
     * @return bool
     */
    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return !$argument->isVariadic() && $request->request->has($argument->getName());
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param \Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata $argument
     *
     * @return \Generator<array|string|int|bool|float|null>
     */
    public function resolve(Request $request, ArgumentMetadata $argument): iterable
    {
        yield $request->request->get($argument->getName());
    }
}
