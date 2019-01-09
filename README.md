# Slim framework HTTP Smoke testing

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This simple package will load ALL your routes from `Slim\App` and make
a Request on them to receive Response to assert expected return http code.

It is not very smart or bullet-proof check, but it
will simply tell you: *does it run?*.

After initial configuration it is almost maintenance-free as it checks any new
routes automatically.

Inspired by
[shopsys/http-smoke-testing](https://github.com/shopsys/http-smoke-testing)
THANK YOU!

## Install

Via Composer

``` bash
composer require --dev jdolba/slim-http-smoke-testing
```

This package internally uses PHPUnit to run the tests.
That means that you need to setup your phpunit.xml properly.

### WARNING
***`Because this package will make a real Request`***
***`be sure you are NOT executing this test on production db!`***


## Usage

Create new PHPUnit test extending
`\JDolba\SlimHttpSmokeTesting\SlimApplicationHttpSmokeTestCase`
class and implement `setUpSmokeTestAndCallConfigure` and `customize` methods.

You can run your new test by:

``` bash
php vendor/bin/phpunit tests/Smoke/MyAwesomeApplicationSmokeTest.php
```

[See example test class](example/tests/MyAwesomeApplicationSmokeTest.php)

### About RequestDataSet

Each your route uri + acceptable http method is represented as
`\JDolba\SlimHttpSmokeTesting\RequestDataSet`
so for example
```
$app = new \Slim\App();
$app->any('/', function ($request, $response, $args) {
//...
return $response;
});
```
will be interpreted as 6 independent DataSets, because Slim is using for "any":

`['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS']`

you can customize passed `$request` in your test class using `customize` method.
This 6 data sets will have routeName `'/'`, but


## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email jakub@dolba.cz instead of using the issue tracker.

## Credits

- [Jakub Dolba][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/jdolba/slim-http-smoke-testing.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/jdolba/slim-http-smoke-testing/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/jdolba/slim-http-smoke-testing.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/jdolba/slim-http-smoke-testing.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/jdolba/slim-http-smoke-testing.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/jdolba/slim-http-smoke-testing
[link-travis]: https://travis-ci.org/jdolba/slim-http-smoke-testing
[link-scrutinizer]: https://scrutinizer-ci.com/g/jdolba/slim-http-smoke-testing/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/jdolba/slim-http-smoke-testing
[link-downloads]: https://packagist.org/packages/jdolba/slim-http-smoke-testing
[link-author]: https://github.com/jDolba
[link-contributors]: ../../contributors
