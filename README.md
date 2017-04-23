# laravel-quota

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a Laravel 5 wrapper for [Markus Malkusch's](https://github.com/malkusch) [threadsafe PHP implementation][link-library] of the [Token Bucket algorithm](https://em.wikipedia.org/wiki/Token_bucket).

Use this package in your Laravel 5 application to enforce api usage limits.

# Benefits

+ Easily throttle application resources
+ Simple install and set-up
+ Bi-directional:
    - works for both inbound and outbound API requests
+ Impose multiple concurrent limits on a resource
    - periodic quota
    - bandwith rate limiter
+ Comply with third party usage limits
+ Flexible overlimit strategies:
    - sleep and block
    - throw exception
    - return status
+ Works with many kinds of resources
    - APIs
    - Files
    - Databases
    - Object instances
+ Can be used in:
    - controller methods
    - static helper methods
    - model instances
+ Threadsafe

## Example usage scenario:
- Comply with 3rd party API limits;

``` php

    use Projectmentor\Quota;
    use Projectmentor\Quota\Exceptions\QuotaException;

    class Geocoder {
    ...
        public function retrieve($data)
        {
            //Enforce daily quota of < 2500 requests
            //Throws exception if overlimit
            try{
                Quota::enforce('DAY', 2500, 'daily.quota');
            } catch (QuotaException $e) {
                //do something.
            }

            //Enforce limit of < 50 api requests/sec
            //Blocks until rate conforming
            Quota::enforce('SECOND', 49, 'burst.quota', true);

            //Make api request
            $response = YourAPI::request($data)->get(); 
            ...
        }
        ...
    }
```

## Install

Via Composer
``` bash
$ composer require projectmentor/laravel-quota

```

## Setup
``` php
//TODO:
```

## Usage

``` php
//TODO:
```

# Features/Roadmap

Implementation status for major features of the [bandwidth-throttle/token-bucket][link-library:] library.

+ Storage:
    |     Storage Type     |  Scope  |  Implemented? |
    |----------------------|---------|---------------|
    | FileStorage          | Global  |      YES      |
    | IPCStorage           | Global  |      NO       |
    | MemcacheStorage      | Global  |      NO       |
    | MemcachedStorage     | Global  |      NO       |
    | PDOStorage           | Global  |      NO       |
    | PHPRedisStorage      | Global  |      NO       |
    | PredisStorage        | Global  |      NO       |
    | SessionStorage       | Session |      NO       |
    | SingleProcessStorage | Request |      NO       |

+ Blocking Behavior:
    |   Bucket Type     |  Blocking?  | Implemented? |
    |-------------------|-------------|--------------|
    |  TokenBucket      |    NO       |    YES       |
    |  BlockingConsumer |    YES      |    YES       |


+ Periodic Rates:
    |    Rate      |  Implemented ? |
    |--------------|----------------|
    | microsecond  |     YES        |
    | millisecond  |     YES        |
    | second       |     YES        |
    | minute       |     YES        |
    | hour         |     YES        |
    | day          |     YES        |
    | week         |     YES        |
    | month        |     YES        |
    | year         |     YES        |

+ Exceptions
    |      Throws          | Implemented? |
    |----------------------|--------------|
    | StorageException     |    YES       |
    | TokenBucketException |    YES       |


## Tested Laravel versions
+ 5.2

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

If you are interested in helping to maintain this project, please feel free to reach out and get in [touch][link-author].
All constructive suggestions are welcome!

This is my very first attempt at publishing a Laravel package on github and packagist so please be kind.

Currently need help and advice with:

+ Laravel refactoring, upgrades, and optimization
+ PHP refactoring, upgrades, and optimization
+ Devops e.g: travis-ci, deploy & PR workflows, etc
+ Testing
+ Documentation

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email david@projectmentor.org instead of using the issue tracker.

## Credits

- [David Faith][link-author]
- [All Contributors][link-contributors]

- I used [this package](https://github.com/Jeroen-G/laravel-packager) to generate most of the documentation and scaffolding for this project.

## Donations

If you find this code helpful and you feel generous show some love!
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=59QHKUNJAF3NA)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

If you have an issue with any of this content from a licensing perspective please [share your concerns][link-author].

[link-library]: https://github.com/bandwith-throttle\token-bucket

[ico-version]: https://img.shields.io/packagist/v/projectmentor/quota.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/projectmentor/quota/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/projectmentor/quota.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/projectmentor/quota.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/projectmentor/quota.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/projectmentor/quota
[link-travis]: https://travis-ci.org/projectmentor/quota
[link-scrutinizer]: https://scrutinizer-ci.com/g/projectmentor/quota/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/projectmentor/quota
[link-downloads]: https://packagist.org/packages/projectmentor/quota
[link-author]: https://github.com/projectmentor
[link-contributors]: ../../contributors
