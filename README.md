# Pdfcrowd API client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a API client for pdfcrowd.com. It is based on the [pdfcrowd/pdfcrowd-php](https://github.com/pdfcrowd/pdfcrowd-php) project but adjusted for usage with Composer. It also includes a Laravel 5.* service provider and unit tests.

## Work in progress

This client is still under active development. What needs to be done:
- Improve docs: in what way does this package differ from the original class?
- Improve docs: add docblocks to all setters.
- Improve docs: try to generate docs for complete class?
- Tag a version and release!

## Install

> In order to make requests to the API you need to enable the PHP cURL library.

Via Composer

``` bash
$ composer require swisnl/pdfcrowd-client
```

## Laravel

We provide a service provider so you can use dependency injection of the Pdfcrowd class within your Laravel projects. If you don't use [package auto-discovery](https://laravel-news.com/package-auto-discovery), add the ServiceProvider to the providers array in config/app.php

```php 
Swis\PdfcrowdClient\PdfcrowdServiceProvider::class,
```

Then, publish the config file using:

```php
php artisan vendor:publish --provider="Swis\PdfcrowdClient\PdfcrowdServiceProvider"
```

## Usage

``` php
# instantiate client, Laravel users can use dependency injection
$client = new Pdfcrowd('username', 'api_key');
 
# convert HTML to PDF and output
echo $client->convertHtml($someHtml);
 
# convert URI to PDF and output
echo $client->convertUri('https://google.com');
 
# convert to PDF and write to file
$client->setOutputDestination(fopen('path/to/output.pdf', 'w');
$client->convertHtml($someHtml);
 
# retrieve the amount of available tokens
$tokens = $client->availableTokens();
 
# retrieve the amount of tokens used by the previous conversion
$tokens = $client->getUsedTokens(); 
```

A complete reference by Pdfcrowd is available at [http://pdfcrowd.com/web-html-to-pdf-php/](http://pdfcrowd.com/web-html-to-pdf-php/).

Basic examples are available in [/examples](/examples).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email security@swis.nl instead of using the issue tracker.

## Credits

- [Barry van Veen][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/swisnl/pdfcrowd-client.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/swisnl/pdfcrowd-client/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/swisnl/pdfcrowd-client.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/swisnl/pdfcrowd-client.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/swisnl/pdfcrowd-client.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/swisnl/pdfcrowd-client
[link-travis]: https://travis-ci.org/swisnl/pdfcrowd-client
[link-scrutinizer]: https://scrutinizer-ci.com/g/swisnl/pdfcrowd-client/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/swisnl/pdfcrowd-client
[link-downloads]: https://packagist.org/packages/swisnl/pdfcrowd-client
[link-author]: https://github.com/swisnl
[link-contributors]: ../../contributors
