<?php

declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting;

use PHPUnit\Framework\TestCase;
use Pimple\Container;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

abstract class SlimApplicationHttpSmokeTestCase extends TestCase
{
    /**
     * @var \JDolba\SlimHttpSmokeTesting\Configuration
     */
    private static $configuration;

    /**
     * init your application, routes, router, etc, you MUST call here configure()
     * @see \JDolba\SlimHttpSmokeTesting\SlimApplicationHttpSmokeTestCase::configure()
     */
    abstract public static function setUpSmokeTestAndCallConfigure(): void;

    /**
     * here you should customize your RequestDataSets
     *
     * @param \JDolba\SlimHttpSmokeTesting\DataSetCustomizer $requestCustomizer
     */
    abstract protected function customize(DataSetCustomizer $requestCustomizer): void;

    /**
     * @param \JDolba\SlimHttpSmokeTesting\Configuration $smokeConfiguration
     */
    final public static function configure(Configuration $smokeConfiguration): void
    {
        self::$configuration = $smokeConfiguration;
    }

    private function throwNotConfiguredException(): void
    {
        throw new \LogicException(
            sprintf(
                'You must call %s::configure before any test is executed',
                __CLASS__
            )
        );
    }

    /**
     * @return array<array<\Psr\Http\Message\RequestInterface|\JDolba\SlimHttpSmokeTesting\RequestDataSet>>
     */
    final public function httpResponseDataProvider(): array
    {
        static::setUpSmokeTestAndCallConfigure();

        if (self::$configuration === null) {
            $this->throwNotConfiguredException();
        }

        /** @var \JDolba\SlimHttpSmokeTesting\RequestDataSet[] $dataSets */
        $dataSets = [];
        foreach (self::$configuration->getRouterAdapter()->getRouteConfigurations() as $routeConfiguration) {
            foreach ($routeConfiguration->getMethods() as $method) {
                $dataSets[] = new RequestDataSet($routeConfiguration, $method);
            }
        }

        $this->customize(
            new DataSetCustomizer($dataSets)
        );

        $allRequestDataSets = [];
        foreach ($dataSets as $dataSet) {
            $allRequestDataSets[] = $dataSet;
            foreach ($dataSet->getAdditionalRequestDataSet() as $additionalRequestDataSet) {
                $allRequestDataSets[] = $additionalRequestDataSet;
            }
        }
        unset($dataSets);

        return array_map(
            function (RequestDataSet $dataSet) {
                $requestPromise = $dataSet->getRequestPromise();
                if (!is_callable($requestPromise)) {
                    throw new \LogicException(
                        sprintf(
                            'RequestPromise in RequestDataSet must be callable and must return %s (on route %s)',
                            RequestInterface::class,
                            $dataSet->getRouteConfiguration()->getRoute()->getPattern()
                        )
                    );
                }

                /** @var \Slim\Http\Request $request */
                $request = $requestPromise(self::$configuration->getRouterAdapter(), $dataSet);
                if (!$request instanceof RequestInterface) {
                    throw new \LogicException(sprintf(
                        '%s expected, %s given from DataSet request promise for route %s',
                        RequestInterface::class,
                        get_class($request),
                        $dataSet->getRouteConfiguration()->getRoute()->getPattern()
                    ));
                }

                $authenticationCallback = $dataSet->getAuthenticationCallback();
                if ($authenticationCallback !== null) {
                    $request = $authenticationCallback($request);

                    if (!$request instanceof RequestInterface) {
                        throw new \LogicException(
                            sprintf(
                                '%s expected, %s given from DataSet authentication callback for route %s',
                                RequestInterface::class,
                                get_class($request),
                                $dataSet->getRouteConfiguration()->getRoute()->getPattern()
                            )
                        );
                    }
                }

                return [
                    $request,
                    $dataSet
                ];
            },
            $allRequestDataSets
        );
    }

    /**
     * @dataProvider httpResponseDataProvider
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \JDolba\SlimHttpSmokeTesting\RequestDataSet $requestDataSet
     */
    final public function testHttpResponse(RequestInterface $request, RequestDataSet $requestDataSet): void
    {
        if (self::$configuration === null) {
            $this->throwNotConfiguredException();
        }

        if ($requestDataSet->isSkipped()) {
            $this->markTestSkipped(
                sprintf(
                    'Route %s (%s) is skipped. %s',
                    $requestDataSet->getRouteConfiguration()->getRoute()->getPattern(),
                    $requestDataSet->getMethod(),
                    ($requestDataSet->getSkippedReason() ?: 'Reason not provided')
                )
            );
        }

        $response = $this->handleRequest($request);

        $this->assertResponse(
            $response,
            $request,
            $requestDataSet
        );
    }

    /**
     * @param \Psr\Http\Message\RequestInterface $request
     * @return \Psr\Http\Message\ResponseInterface
     */
    protected function handleRequest(RequestInterface $request)
    {
        $application = self::$configuration->getApp();
        $container = $application->getContainer();

        if (method_exists($container, 'set')) {
            /**
             * @see https://php-di.org/
             * @see \DI\Container
             */
            $container->set('request', $request);
        } elseif (method_exists($container, 'offsetSet')) {
            /**
             * default Slim3 Container implementation
             * @see \Pimple\Container
             */
            $container->offsetSet('request', $request);
        } else {
            throw new \LogicException('Unsupported Container method. Your Container implementation does not support "set"');
        }

        return $application->run(true);
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \JDolba\SlimHttpSmokeTesting\RequestDataSet $requestDataSet
     */
    protected function assertResponse(
        ResponseInterface $response,
        RequestInterface $request,
        RequestDataSet $requestDataSet
    ): void {
        $failureMessage = sprintf(
            'Response code %d for route %s is not identical to expected %d',
            $response->getStatusCode(),
            $request->getRequestTarget(),
            $requestDataSet->getExpectedHttpCode()
        );

        $this->assertSame(
            $response->getStatusCode(),
            $requestDataSet->getExpectedHttpCode(),
            $failureMessage
        );
    }
}
