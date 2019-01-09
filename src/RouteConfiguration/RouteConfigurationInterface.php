<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting\RouteConfiguration;

use Slim\Interfaces\RouteInterface;

interface RouteConfigurationInterface
{
    public function getRoute(): RouteInterface;

    public function getRouteName(): string;

    /**
     * @return string[]
     */
    public function getMethods(): array;
}
