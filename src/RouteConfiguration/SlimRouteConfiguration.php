<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting\RouteConfiguration;

use Slim\Interfaces\RouteInterface;
use Slim\Route;

class SlimRouteConfiguration implements RouteConfigurationInterface
{

    /**
     * @var \Slim\Route
     */
    private $route;

    public function __construct(Route $route)
    {
        $this->route = $route;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    public function getRouteName(): string
    {
        return  $this->route->getPattern();
    }

    public function getMethods(): array
    {
        return $this->route->getMethods();
    }
}
