<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting\RouterAdapter;

use JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface;

interface RouterAdapterInterface
{
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
    ): string;

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface[]
     */
    public function getRouteConfigurations(): array;
}
