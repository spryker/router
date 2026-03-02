<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Shared\Router;

use Codeception\Actor;
use Spryker\Service\Container\Container;
use Spryker\Service\Container\ContainerDelegator;
use Spryker\Service\Container\ContainerInterface;
use Spryker\Shared\Router\Resolver\ControllerResolver;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolverInterface;

/**
 * @method void wantToTest($text)
 * @method void wantTo($text)
 * @method void execute($callable)
 * @method void expectTo($prediction)
 * @method void expect($prediction)
 * @method void amGoingTo($argumentation)
 * @method void am($role)
 * @method void lookForwardTo($achieveValue)
 * @method void comment($description)
 * @method void pause()
 *
 * @SuppressWarnings(\SprykerTest\Shared\Router\PHPMD)
 */
class RouterTester extends Actor
{
    use _generated\RouterTesterActions;

    /**
     * @var array
     */
    protected $calledControllerMethods = [];

    public function getMockControllerNamespace(): string
    {
        return 'Spryker\Zed\Router\Communication\Controller\MockController';
    }

    public function addCalledControllerMethod(string $methodName): void
    {
        $this->calledControllerMethods[$methodName] = true;
    }

    public function getControllerResolver(array $services = []): ControllerResolverInterface
    {
        return new ControllerResolver(new Container($services));
    }

    public function getRequest(): Request
    {
        return Request::createFromGlobals();
    }

    public function getRequestWithUnresolvableController(): Request
    {
        $request = $this->getRequest();

        $request->attributes->set('_controller', 123);

        return $request;
    }

    public function getRequestWithCallableController(): Request
    {
        $request = $this->getRequest();
        $request->attributes->set('_controller', [
            function () {
                $controller = $this->getMockControllerNamespace();

                return new $controller();
            },
            'mockAction',
        ]);

        return $request;
    }

    public function getRequestWithControllerService(): Request
    {
        $request = $this->getRequest();
        $request->attributes->set('_controller', 'ControllerServiceName:mockAction');

        return $request;
    }

    public function getRequestWithInvalidControllerString(): Request
    {
        $request = $this->getRequest();
        $request->attributes->set('_controller', 'invalid-string');

        return $request;
    }

    public function getRequestWithControllerUrl(): Request
    {
        $request = $this->getRequest();
        $request->attributes->set('_controller', '/router/mock/mock');

        return $request;
    }

    public function getRequestWithInstantiableClass(): Request
    {
        $request = $this->getRequest();
        $request->attributes->set('_controller', [$this->getMockControllerNamespace(), 'mockAction']);

        return $request;
    }

    public function getRequestWithInvokableControllerObject(): Request
    {
        $request = $this->getRequest();

        $controllerMock = $this->getInvokableControllerMock($this);
        $request->attributes->set('_controller', $controllerMock);

        return $request;
    }

    public function getRequestWithNotInvokableControllerObject(): Request
    {
        $request = $this->getRequest();
        $controllerMock = new class
        {
        };
        $request->attributes->set('_controller', $controllerMock);

        return $request;
    }

    public function getInvokableControllerMock(RouterTester $tester): callable
    {
        $this->calledControllerMethods = [];

        return new class ($tester)
        {
            /**
             * @var \SprykerTest\Shared\Router\RouterTester
             */
            protected $tester;

            /**
             * @var \Spryker\Service\Container\ContainerInterface
             */
            protected $container;

            public function __construct(RouterTester $tester)
            {
                $this->tester = $tester;
            }

            public function setApplication(ContainerInterface $container): void
            {
                $this->container = $container;

                $this->tester->addCalledControllerMethod('setApplication');
            }

            public function initialize(): void
            {
                $this->tester->addCalledControllerMethod('initialize');
            }

            public function __invoke(): string
            {
                $this->tester->addCalledControllerMethod('__invoke');

                return 'Controller';
            }
        };
    }

    public function assertController(string $controller, array $resolvedController): void
    {
        if (is_object($resolvedController[0])) {
            $resolvedController[0] = get_class($resolvedController[0]);
        }

        $this->assertSame($controller, $resolvedController[0]);
    }

    public function assertSetApplicationAndInitializeCalledOnController(): void
    {
        $this->assertTrue(isset($this->calledControllerMethods['setApplication']));
        $this->assertTrue(isset($this->calledControllerMethods['initialize']));
    }

    public function assertInvokeCalledOnController(): void
    {
        $this->assertTrue(isset($this->calledControllerMethods['setApplication']));
        $this->assertTrue(isset($this->calledControllerMethods['initialize']));
    }

    public function getInitializableTestControllerNamespace(): string
    {
        return 'Spryker\Zed\Router\Communication\Controller\InitializableTestController';
    }

    public function assertSetApplicationAndInitializeCalledOnTestController(): void
    {
        require_once codecept_data_dir('Fixtures/Controller/InitializableTestController.php');

        $controllerClass = $this->getInitializableTestControllerNamespace();

        $this->assertTrue(isset($controllerClass::$calledMethods['setApplication']));
        $this->assertTrue(isset($controllerClass::$calledMethods['initialize']));

        $controllerClass::resetCalledMethods();
    }

    public function getRequestWithControllerInGlobalContainer(): Request
    {
        $request = $this->getRequest();

        $request->attributes->set('_controller', 'GlobalController.ServiceName');

        return $request;
    }

    public function getControllerResolverWithGlobalContainer(): ControllerResolverInterface
    {
        require_once codecept_data_dir('Fixtures/Controller/InitializableTestController.php');

        $controllerClass = $this->getInitializableTestControllerNamespace();
        $controllerInstance = new $controllerClass();

        $globalContainer = ContainerDelegator::getInstance();
        $globalContainer->set('GlobalController.ServiceName', $controllerInstance);

        return new ControllerResolver(new Container());
    }

    public function getRequestWithControllerInNestedContainer(): Request
    {
        $request = $this->getRequest();

        $request->attributes->set('_controller', 'NestedControllerServiceName:mockAction');

        return $request;
    }

    public function getControllerResolverWithNestedContainer(): ControllerResolverInterface
    {
        require_once codecept_data_dir('Fixtures/Controller/InitializableTestController.php');

        $controllerClass = $this->getInitializableTestControllerNamespace();

        $nestedContainer = new Container(['NestedControllerServiceName' => $controllerClass]);
        $containerWrapper = new Container(['container' => $nestedContainer]);

        return new ControllerResolver($containerWrapper);
    }

    public function getRequestWithControllerArrayFromDelegator(): Request
    {
        $request = $this->getRequest();

        $request->attributes->set('_controller', ['DelegatorControllerServiceName', 'mockAction']);

        return $request;
    }

    public function getControllerResolverWithDelegatorService(): ControllerResolverInterface
    {
        require_once codecept_data_dir('Fixtures/Controller/InitializableTestController.php');

        $controllerClass = $this->getInitializableTestControllerNamespace();
        $controllerInstance = new $controllerClass();

        $delegator = ContainerDelegator::getInstance();
        $delegator->set('DelegatorControllerServiceName', $controllerInstance);

        $container = new Container([ContainerDelegator::class => $delegator]);

        return new ControllerResolver($container);
    }
}
