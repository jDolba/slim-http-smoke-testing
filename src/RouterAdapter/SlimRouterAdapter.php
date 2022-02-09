<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting\RouterAdapter;

use JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface;
use JDolba\SlimHttpSmokeTesting\RouteConfiguration\SlimRouteConfiguration;
use Slim\Route;
use Slim\Router;

class SlimRouterAdapter implements RouterAdapterInterface
{
    /**
     * @var \Slim\Router
     */
    private $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface[]
     */
    public function getRouteConfigurations(): array
    {
        $configurations = [];
        foreach ($this->router->getRoutes() as $route) {
            $routeConfiguration = new SlimRouteConfiguration($route);
            $route->setName($routeConfiguration->getRouteName());
            $configurations[] = $routeConfiguration;
        }

        return $configurations;
    }

    /**
     * @param \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface $routeConfiguration
     * @param array<string, int> $uriParams params passed to generate URI part; keys are name of params in URI
     * @param array<string, int> $queryParams not mandatory query parts (to be used like ?foo=bar)
     * @return string
     */
    public function generateRelativePath(
        RouteConfigurationInterface $routeConfiguration,
        array $uriParams = [],
        array $queryParams = []
    ): string {
        return $this->router->relativePathFor(
            $routeConfiguration->getRouteName(),
            $uriParams,
            $queryParams
        );
    }
}
