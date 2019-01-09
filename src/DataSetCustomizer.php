<?php
declare(strict_types=1);

namespace JDolba\SlimHttpSmokeTesting;

class DataSetCustomizer
{

    /**
     * @var \JDolba\SlimHttpSmokeTesting\RequestDataSet[]
     */
    private $dataSets;

    public function __construct(array $dataSets)
    {
        $this->dataSets = $dataSets;
    }

    /**
     * invoke callback on all RequestDataSets
     * @param callable $callback
     * @return $this
     */
    public function customize(callable $callback): self
    {
        foreach ($this->dataSets as $dataSet) {
            $callback(
                $dataSet
            );
        }

        return $this;
    }

    /**
     * invoke callback on RequestDataSets matching routeName
     * @see \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface::getRouteName()
     *
     * @param string $routeName
     * @param callable $callback
     * @return $this
     */
    public function customizeByRouteName(string $routeName, callable $callback): self
    {
        $this->customizeByConditionCallback(
            function (RequestDataSet $dataSet) use ($routeName) {
                return $dataSet->getRouteConfiguration()->getRouteName() === $routeName;
            },
            $callback
        );

        return $this;
    }

    /**
     * invoke callback on RequestDataSets matching routeName and http method
     * @see \JDolba\SlimHttpSmokeTesting\RouteConfiguration\RouteConfigurationInterface::getRouteName()
     * @see \JDolba\SlimHttpSmokeTesting\RequestDataSet::getMethod()
     *
     * @param string $routeName
     * @param string $method
     * @param callable $callback
     * @return $this
     */
    public function customizeByRouteNameAndMethod(string $routeName, string $method, callable $callback)
    {
        $this->customizeByConditionCallback(
            function (RequestDataSet $dataSet) use ($routeName, $method) {
                return $dataSet->getMethod() === $method
                    && $dataSet->getRouteConfiguration()->getRouteName() === $routeName;
            },
            $callback
        );

        return $this;
    }

    /**
     * invoke callback on RequestDataSet if conditionCallback on same RequestDataSet is evaluated as true
     *
     * @param callable $conditionCallback
     * @param callable $callback
     * @return $this
     */
    public function customizeByConditionCallback(callable $conditionCallback, callable $callback)
    {
        $found = [];
        foreach ($this->dataSets as $dataSet) {
            if (true === $conditionCallback($dataSet)) {
                $found[] = $dataSet;
            }
        }

        if (count($found) === 0) {
            throw new \InvalidArgumentException('No DataSet found by given callback');
        }

        foreach ($found as $dataSet) {
            $callback($dataSet);
        }

        return $this;
    }
}
