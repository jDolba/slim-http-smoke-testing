<?php

use JDolba\SlimHttpSmokeTesting\Configuration;
use JDolba\SlimHttpSmokeTesting\DataSetCustomizer;
use JDolba\SlimHttpSmokeTesting\RequestDataSet;
use JDolba\SlimHttpSmokeTesting\RouterAdapter\RouterAdapterInterface;
use JDolba\SlimHttpSmokeTesting\RouterAdapter\SlimRouterAdapter;
use JDolba\SlimHttpSmokeTesting\SlimApplicationHttpSmokeTestCase;
use Slim\App;
use Slim\Http\Environment;
use Slim\Http\Request;
use Slim\Http\Response;

class MyAwesomeApplicationSmokeTest extends SlimApplicationHttpSmokeTestCase
{
    public static function setUpSmokeTestAndCallConfigure(): void
    {
        // $app = new App(); // retrieve your App with configured routes, container etc.
        // TODO: be sure that App is configured for testing environment(database)

        $app = self::configureAppRoutesForExamplePurpose();

        self::configure(
            new Configuration(
                $app,
                new SlimRouterAdapter($app->getContainer()->get('router'))
            )
        );
    }

    protected function customize(DataSetCustomizer $customizer): void
    {
        // it is important to realize, that RequestDataSet = route + method
        // example: [ GET /api/user, POST /api/user ] are 2 instance of RequestDataSet
        // each DataSet may have additional DataSet, which will use same Route, but different Request

        $customizer
            // customize callback will be applied on ALL data sets (i.e. all routes with all methods)
            ->customize(
                function (RequestDataSet $dataSet) {
                    if ($dataSet->getMethod() !== 'GET') {
                        $dataSet->skip('We are lazy and we will smoke-test only routes which are using GET');
                    }

                    $dataSet->setAuthenticationCallback(function (Request $request) {
                        // here you can inject some authentication credentials into Request headers etc.
                        // authenticationCallback is invoked AFTER creating Request from requestPromise method
                        return $request;
                    });
                }
            )
            // example of modifying route with named parameters (URI params)
            // it will use Router implementation by your Configuration to generate uri
            ->customizeByRouteName(
                '/api/user/find-by-id/{userId}',
                function (RequestDataSet $dataSet) {
                    $dataSet->setUriParamsForRouter(
                        [
                            'userId' => 42, // will create /api/user/find-by-id/42
                        ]
                    );
                }
            )
            // also you can use query params and add more RequestDataSet to test same route with more data-examples
            ->customizeByRouteNameAndMethod(
                '/api/user',
                'GET',
                function (RequestDataSet $dataSet) {
                    $dataSet->setQueryParamsForRouter(
                        [
                            'userId' => 42, // will create /api/user?userId=42
                        ]
                    );

                    $newRequestDataSet = $dataSet->addNewExtraRequestDataSet();
                    // method addNewExtraRequestDataSet returns new instance of RequestDataSet so you can modify it here
                    // additional data sets are NOT matched by all customize* functions
                    $newRequestDataSet->setQueryParamsForRouter(
                        [
                            'userId' => 96, // will create another request on same route /api/user?userId=96
                        ]
                    );
                    $newRequestDataSet->setExpectedHttpCode(404);
                }
            )
            // more complicated example with custom request and custom condition to match RequestDataSet
            ->customizeByConditionCallback(
                function (RequestDataSet $dataSet) {
                    // here you can put any condition you like to find your desired data set
                    return $dataSet->getMethod() === 'POST' // route is defined to accept only post
                        && $dataSet->getRouteConfiguration()->getRouteName() === '/api/user';
                },
                function (RequestDataSet $dataSet) {
                    $dataSet->asNotSkipped(); // all non-GET routes were skipped by previous customization rule

                    // RequestDataSet is using callback to create Request and you can use your own RequestPromise
                    // there is only simple RequestPromise callback by default, so for complicated requests you must
                    // create your own Request using setRequestPromise method, for example as below:
                    $dataSet->setRequestPromise(
                        function (RouterAdapterInterface $routerAdapter, RequestDataSet $dataSet) {
                            $uri = $routerAdapter->generateRelativePath(
                                $dataSet->getRouteConfiguration(),
                                $dataSet->getUriParamsForRouter(),
                                $dataSet->getQueryParamsForRouter()
                            );

                            $env = Environment::mock(
                                [
                                    'REQUEST_METHOD' => $dataSet->getMethod(),
                                    'REQUEST_URI' => $uri,
                                ]
                            );
                            $request = Request::createFromEnvironment($env);
                            // this is standard way how to mock Requests for Slim

                            // lets say we are POSTing data to create new user
                            // don't forget that Request implementation is IMMUTABLE
                            return $request->withParsedBody(
                                [
                                    'name' => 'Johny Walker',
                                    'email' => 'johny@wolker.com',
                                ]
                            );
                        }
                    );

                    $newRequest = $dataSet->addNewExtraRequestDataSet();
                    $newRequest->setRequestPromise($dataSet->createDefaultRequestPromise());
                    $newRequest->setExpectedHttpCode(500);
                }
            );
    }

    /**
     * will configure base routes for example application in this test
     *
     * @return  \Slim\App $app
     */
    private static function configureAppRoutesForExamplePurpose(): App
    {
        $app = new App([
            'settings' => [
                'displayErrorDetails' => true,
            ]
        ]);

        $app->any(
            '/',
            function (Request $request, Response $response) {
                return $response;
            }
        );
        $app->get(
            '/api/user/find-by-id/{userId}',
            function (Request $request, Response $response, $args) {
                if ($args['userId'] == 42) {
                    return $response->withJson(['ok']);
                }

                return $response->withJson(['not found'])->withStatus(404);
            }
        );
        $app->get(
            '/api/user',
            function (Request $request, Response $response) {

                if ($request->getQueryParam('userId') == 42) {
                    return $response->withJson(['ok']);
                } else {
                    return $response->withJson(['not found'])->withStatus(404);
                }

            }
        );
        $app->post(
            '/api/user',
            function (Request $request, Response $response) {
                if (
                    $request->getParam('name') === 'Johny Walker'
                    && $request->getParam('email') === 'johny@wolker.com'
                ) {
                    return $response->withJson('ok');
                }

                throw new \RuntimeException('MyAwesomeApplication has runtime exception');
            }
        );

        return $app;
    }
}
