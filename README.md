# Pdfcrowd API client

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a API client for pdfcrowd.com. It is based on the [pdfcrowd/pdfcrowd-php](https://github.com/pdfcrowd/pdfcrowd-php) project but adjusted for usage with Composer. It also includes a Laravel 5.* service provider and unit tests.

## Install

Via Composer

``` bash
$ composer require swisnl/pdfcrowd-client
```

If you are using Laravel 5.0 - 5.4, add the following line to providers in `config/app.php`
```php

```

## Usage

``` php
$skeleton = new Swis\PdfcrowdClient();
echo $skeleton->echoPhrase('Hello, League!');
```

A complete reference by Pdfcrowd is available at [http://pdfcrowd.com/web-html-to-pdf-php/](http://pdfcrowd.com/web-html-to-pdf-php/).

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CODE_OF_CONDUCT](CODE_OF_CONDUCT.md) for details.

## Security

If you discover any security related issues, please email barry@swis.nl instead of using the issue tracker.

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
