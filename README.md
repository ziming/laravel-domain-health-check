# Domain Health Check for Laravel Health and Oh Dear

[![Latest Version on Packagist](https://img.shields.io/packagist/v/ziming/laravel-domain-health-check.svg?style=flat-square)](https://packagist.org/packages/ziming/laravel-domain-health-check)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/ziming/laravel-domain-health-check/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/ziming/laravel-domain-health-check/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/ziming/laravel-domain-health-check/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/ziming/laravel-domain-health-check/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/ziming/laravel-domain-health-check.svg?style=flat-square)](https://packagist.org/packages/ziming/laravel-domain-health-check)

Domain Health Check for [Spatie Laravel Health](https://github.com/spatie/laravel-health) Package. Which also works with
[Oh Dear](https://ohdear.app/?via=laravel-health-domain-check) monitoring service.

Currently it uses the Whois protocol to fetch the domain expiry datetime. This fills a missing gap in [Oh Dear](https://ohdear.app/?via=laravel-health-domain-check) as Oh Dear
only supports RDAP domain expiry checks at the moment, which does not work for many TLDs.

So by using this package, you get to monitor your domain expiry dates in both Laravel Health and Oh Dear.

In the future this package may support RDAP domain expiry check too.

## Support me

You can donate to my GitHub sponsor or use my referral link for [Oh Dear](https://ohdear.app/?via=laravel-health-domain-check) so I get a small reward if you become a paid customer in the future. This comes at no extra cost to you and helps support my open source work.

https://ohdear.app/?via=laravel-health-domain-check

## Installation

You can install the package via composer:

```bash
composer require ziming/laravel-domain-health-check
```

## Usage

```php
// In your Laravel Health Service Provider register() method

use Spatie\Health\Facades\Health;
use Ziming\LaravelDomainHealthCheckHealthCheck\DomainCheck;

Health::checks([
    DomainCheck::new()
        ->domain('example.com') // by default, it uses your app.url config host if you did not call this method
        ->warnWhenDaysLeftToDomainExpiry(28)
        ->failWhenDaysLeftToDomainExpiry(7)
        ->daily(),
]);
```

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [ziming](https://github.com/ziming)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
