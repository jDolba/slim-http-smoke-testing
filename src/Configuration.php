<?php

namespace JDolba\SlimHttpSmokeTesting;

use JDolba\SlimHttpSmokeTesting\RouterAdapter\RouterAdapterInterface;
use Slim\App;

final class Configuration
{

    /**
     * @var App
     */
    private $app;
    /**
     * @var RouterAdapterInterface
     */
    private $routerAdapter;

    public function __construct(App $app, RouterAdapterInterface $routerAdapter)
    {
        $this->app = $app;
        $this->routerAdapter = $routerAdapter;
    }

    /**
     * @return \Slim\App
     */
    public function getApp(): App
    {
        return $this->app;
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RouterAdapter\RouterAdapterInterface
     */
    public function getRouterAdapter(): RouterAdapterInterface
    {
        return $this->routerAdapter;
    }
}
