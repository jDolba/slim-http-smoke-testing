<?php

namespace Tests\JDolba\SlimHttpSmokeTesting;

use JDolba\SlimHttpSmokeTesting\DataSetCustomizer;
use JDolba\SlimHttpSmokeTesting\RequestDataSet;
use JDolba\SlimHttpSmokeTesting\RouteConfiguration\SlimRouteConfiguration;
use PHPUnit\Framework\TestCase;

class DataSetCustomizerTest extends TestCase
{
    /**
     * @dataProvider customizeDataProvider
     *
     * @param \JDolba\SlimHttpSmokeTesting\RequestDataSet[] $dataSets
     */
    public function testCustomizeCallback(array $dataSets): void
    {
        $customizer = new DataSetCustomizer($dataSets);

        $newExpectedCode = 519;
        $customizer->customize(function (RequestDataSet $dataSet) use ($newExpectedCode) {
            $dataSet->setExpectedHttpCode($newExpectedCode);
        });

        foreach ($dataSets as $dataSet) {
            $this->assertEquals($newExpectedCode, $dataSet->getExpectedHttpCode());
        }
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RequestDataSet[][][]
     */
    public function customizeDataProvider(): array
    {
        $routeConfigMock = $this->createMock(SlimRouteConfiguration::class);

        $dataSet = [
            new RequestDataSet($routeConfigMock, 'GET'),
            new RequestDataSet($routeConfigMock, 'POST'),
            new RequestDataSet($routeConfigMock, 'OPTION'),
        ];

        return [
            [$dataSet],
        ];
    }

    /**
     * @dataProvider customizeByConditionCallbackDataProvider
     *
     * @param \JDolba\SlimHttpSmokeTesting\RequestDataSet[] $dataSets
     */
    public function testCustomizeByConditionCallback(array $dataSets): void
    {
        $customizer = new DataSetCustomizer($dataSets);

        $customizer->customizeByConditionCallback(
            function (RequestDataSet $dataSet) {
                return $dataSet->getMethod() === 'POST';
            },
            function (RequestDataSet $dataSet) {
                $dataSet->skip('Because it is POST');
            }
        );

        foreach ($dataSets as $dataSet) {
            if ($dataSet->getMethod() === 'POST') {
                $this->assertTrue(
                    $dataSet->isSkipped()
                );
            } else {
                $this->assertFalse(
                    $dataSet->isSkipped()
                );
            }
        }
    }

    /**
     * @return \JDolba\SlimHttpSmokeTesting\RequestDataSet[][][]
     */
    public function customizeByConditionCallbackDataProvider(): array
    {
        $routes = [
            '/v1/user',
            '/v1/languages'
        ];

        $dataSets = [];
        foreach ($routes as $route) {
            $routeConfigMock = $this->createMock(SlimRouteConfiguration::class);

            $routeConfigMock->method('getRouteName')
                ->willReturn($route);

            foreach (['GET', 'POST'] as $method) {
                $dataSets[] = new RequestDataSet($routeConfigMock, $method);
            }
        }

        return [
            [$dataSets],
        ];
    }
}
