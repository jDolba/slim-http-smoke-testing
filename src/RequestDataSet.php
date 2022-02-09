<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting;

use JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface;
use JDolba\SlimHttpSmokeTesting\RouterAdapter\RouterAdapterInterface;
use Slim\Http\Environment;
use Slim\Http\Request;

class RequestDataSet
{
    const DEFAULT_EXPECTED_HTTP_CODE_OK = 200;

    /**
     * @var int
     */
    private $expectedHttpCode = self::DEFAULT_EXPECTED_HTTP_CODE_OK;
    /**
     * @var \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface
     */
    private $routeConfiguration;
    /**
     * @var string
     */
    private $method;
    /**
     * @var bool
     */
    private $skipped;
    /**
     * @var null|string
     */
    private $skippedReason;
    /**
     * @var array<string, int>
     */
    private $uriParamsForRouter = [];
    /**
     * @var array<string, int>
     */
    private $queryParamsForRouter = [];
    /**
     * @var callable
     */
    private $requestPromise;
    /**
     * @var null|callable
     */
    private $authenticationCallable;
    /**
     * @var self[]
     */
    private $additionalRequestDataSets = [];

    public function __construct(RouteConfigurationInterface $routeConfiguration, string $method)
    {
        $this->method = $method;
        $this->routeConfiguration = $routeConfiguration;
        $this->asNotSkipped();
        $this->requestPromise = $this->createDefaultRequestPromise();
    }

    public function createDefaultRequestPromise(): \Closure
    {
        return function (RouterAdapterInterface $routerAdapter, RequestDataSet $requestDataSet) {
            $uri = $routerAdapter->generateRelativePath(
                $requestDataSet->getRouteConfiguration(),
                $requestDataSet->getUriParamsForRouter(),
                $requestDataSet->getQueryParamsForRouter()
            );

            $env = Environment::mock(
                [
                    'REQUEST_METHOD' => $requestDataSet->getMethod(),
                    'REQUEST_URI' => $uri,
                ]
            );

            return Request::createFromEnvironment($env);
        };
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return array<string, int>
     */
    public function getUriParamsForRouter(): array
    {
        return $this->uriParamsForRouter;
    }

    /**
     * @param array<string, int> $uriParams
     */
    public function setUriParamsForRouter(array $uriParams): void
    {
        $this->uriParamsForRouter = $uriParams;
    }

    /**
     * @return array<string, int>
     */
    public function getQueryParamsForRouter(): array
    {
        return $this->queryParamsForRouter;
    }

    /**
     * @param array<string, int> $queryParams
     */
    public function setQueryParamsForRouter(array $queryParams): void
    {
        $this->queryParamsForRouter = $queryParams;
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface
     */
    public function getRouteConfiguration(): RouteConfigurationInterface
    {
        return $this->routeConfiguration;
    }

    /**
     * @return int
     */
    public function getExpectedHttpCode(): int
    {
        return $this->expectedHttpCode;
    }

    public function setExpectedHttpCode(int $code): void
    {
        $this->expectedHttpCode = $code;
    }

    public function skip(?string $why = null): void
    {
        $this->skipped = true;
        $this->skippedReason = $why;
    }

    public function asNotSkipped(): void
    {
        $this->skipped = false;
        $this->skippedReason = null;
    }

    /**
     * @return bool
     */
    public function isSkipped(): bool
    {
        return $this->skipped;
    }

    /**
     * @return null|string
     */
    public function getSkippedReason(): ?string
    {
        return $this->skippedReason;
    }

    /**
     * arguments of callable will be:
     * + \JDolba\SlimHttpSmokeTesting\RouterAdapter\RouterAdapterInterface $routerAdapter
     * + \JDolba\SlimHttpSmokeTesting\RequestDataSet $requestDataSet ($this)
     *
     * @see \JDolba\SlimHttpSmokeTesting\RequestDataSet::__construct
     *
     * @param callable $callable
     */
    public function setRequestPromise(callable $callable): void
    {
        $this->requestPromise = $callable;
    }

    /**
     * @return callable
     */
    public function getRequestPromise(): callable
    {
        return $this->requestPromise;
    }

    /**
     * @param string|null $method if null, value of $method from parent(this) will be used
     * @return self new instance for additional request on same route
     */
    public function addNewExtraRequestDataSet(string $method = null)
    {
        $newRequestDataSet = new self(
            $this->getRouteConfiguration(),
            $method === null ? $this->getMethod() : $method
        );

        $this->additionalRequestDataSets[] = $newRequestDataSet;

        return $newRequestDataSet;
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RequestDataSet[]
     */
    public function getAdditionalRequestDataSet(): array
    {
        return $this->additionalRequestDataSets;
    }

    /**
     * Request from setRequestPromise will be passed
     *
     * @param callable $callable
     */
    public function setAuthenticationCallback(callable $callable): void
    {
        $this->authenticationCallable = $callable;
    }

    /**
     * @return callable|null
     */
    public function getAuthenticationCallback(): ?callable
    {
        return $this->authenticationCallable;
    }
}
