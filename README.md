# laravel-quota

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This is a Laravel 5 wrapper for [@malkusch](https://github.com/malkusch) [threadsafe PHP implementation][link-library] of the [Token Bucket algorithm](https://em.wikipedia.org/wiki/Token_bucket).

Use this package in a Laravel 5 application to enforce api usage limits.

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

## Example non-blocking usage scenario:

Guarding an api call within a static helper function.
```php
...
class ApiHelper {
    ...
    public static function callApi($bucket, $params) {
    
        //Enforce the quota.
        if(! $bucket->consume(1, $seconds))
          throw new TokenBucketException(
            'Overquota. Retry in: ' .$seconds . ' seconds.');
        
        //Call the api
        return APIFacade::getSomeData($params);
    }
}
```

## Example blocking usage scenario:

Guarding an api call within a static helper function.
```php
...
class ApiHelper {
    ...
    public static function callApi($consumer, $params) {
    
        //Enforce the quota, wait until ready.
        $consumer->consume(1);
        
        //Call the api
        return APIFacade::getSomeData($params);
    }
}
```


## Install

To get the latest version of Laravel Quota, simply require the project using Composer:
``` bash
$ composer require projectmentor/laravel-quota
```
Alternately, you may of course manually update composer.json and run composer update
```php
{
    "require": {
        "projectmentor/laravel-quota": "1.0-dev"
    }
}
```
```bash
$ composer update
```

## Setup

Once Laravel Quota is installed, you will need to register the service provider in your Laravel 5 project.
Open config/app.php and add the following value to the providers array:

``` php
...
'providers' => [
...
    'Projectmentor\Quota\QuotaServiceProvider::class',
],
...
```
Next, in the same file we register the facade as a key value pair in the aliases array:
```php
...
'aliases' => [
...
    'Quota' => Projectmentor\Quota\Facades\Quota::class,
],
...
```
*Note: in the current release the Quota facade is not implemented.*

## Usage

This package uses the namespace Projectmentor\Quota.

The general idea in the first release of this package is to expose the following [library][link-library] classes:

|namespace                             |class name       |
|--------------------------------------|-----------------|
|bandwidthThrottle\tokenBucket\storage |FileStorage      |
|bandwidthThrottle\tokenBucket         |Rate             |
|bandwidthThrottle\tokenBucket         |TokenBucket      |
|bandwidthThrottle\tokenBucket         |BlockingConsumer |

Both the TokenBucket and the BlockingConsumer classes may be used to limit access to a resource.
For the purposes of the following discussion, let's define both classes as `consumers`.

FileStorage and Rate are dependencies of both types of consumer.

BlockingConsumer is a special case of TokenBucket which automatically calculates the amount of time 
and sleeps until the target resource becomes available.

In general, to guard a resource with a quota you might take the following steps:
- Choose either TokenBucket or BlockingConsumer implementation.
- Decide how to manage the state of the quota between application calls and different users.
*In the current release we implement locking and state management using the FileStorage class.*
     - Hence, create a FileStorage instance from a file path.
- Specify a rate limit and a time period for your quota via the Rate class.
- Create a TokenBucket from Rate and storage.
- Bootstrap the TokenBucket with an initial amount of tokens -or-
- Create a BlockingConsumer and bootstrap the TokenBucket if you want blocking behavior.

Once you have created a `consumer`, i.e: TokenBucket or BlockingConsumer, and just prior
to calling the rate-limited resource have the consumer consume() one or more tokens. If there are not
enough tokens remaining in the specified time period, then the consumer can either block, or throw an exception,
or, in the scope of a static function, return a fail code.  

*Remember: TokenBucket won't block when overquota, and you will need to specify what action to take; whereas BlockingConsumer will sleep until enough tokens are available to conform to the quota you are enforcing.*

Here's a quick example of how we instantiate a TokenBucket instance using this package by exposing the underlying classes from the [library][link-library]:

As in the previous examples above, let's imagine we need to enforce an api quota within a static helper function.
Let's explore one way we might accomplish our goal using only the exposed classes, and without relying on the service
container to resolve class dependencies.

``` php
namespace YourName\YourProject\Helpers;

use \bandwidthThrottle\tokenBucket\TokenBucketException;
use \bandwidthThrottle\tokenBucket\TokenBucket;
use \bandwidthThrottle\tokenBucket\Rate;
use \bandwidthThrottle\tokenBucket\storage\FileStorage;

class ApiHelper {

    //Capacity of the bucket in tokens.
    const CAPACITY = 60;
    
    //Path to persistant storage
    const STORAGE_PATH = '/tmp/quota.bucket';

    public static function callApi($params) {
       
       //Set rate limit at 60/Second
        $capacity = self::CAPACITY;
        $period = Rate::SECOND;
        $rate = new Rate($capacity, $period);
        
        //Setup persistant storage to manage state
        $storage = new FileStorage(self::STORAGE_PATH);
        
        //Create a new bucket to access the storage and 
        //compute whether or not to grant access
        $bucket = new TokenBucket($capacity, $rate, $storage);
        
        //Bootstrap the persistant storage
        //If already bootstrapped from a previous call,
        //then no-op.
        $bucket->bootstrap($capacity);    
    
        //Enforce the quota. Don't block.
        if(! $bucket->consume(1))
            throw new TokenBucketException();
    
        //Call the api   
        return APIFacade::getSomeData($params);
    }
}

```
We can also use the Laravel service container create instances of some the models we might need.
Head on over to the [factory tests](https://github.com/projectmentor/laravel-quota/tests) for more examples
of how to create consumers.

## Features roadmap

+ Facade

Hide implementation details of the underlying library using syntax like: 
```php
Quota::enforce(...)
```
*not currently implemented.*

+ Routing and Middleware

Provide the ability to automatically limit resource via controller through middleware.

*not currently implemented.*

+ Manage concurrent quota's

Implement a manager pattern to track and resolve multiple quota instances.

*not currently implemented.*

## Implementation status for major features of the [library][link-library]

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
+ Testing and mocking.
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

[link-library]: https://github.com/bandwidth-throttle/token-bucket

[ico-version]: https://img.shields.io/packagist/v/projectmentor/laravel-quota.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/projectmentor/laravel-quota/master.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/projectmentor/laravel-quota.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/projectmentor/laravel-quota.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/projectmentor/laravel-quota.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/projectmentor/laravel-quota
[link-travis]: https://travis-ci.org/projectmentor/laravel-quota
[link-scrutinizer]: https://scrutinizer-ci.com/g/projectmentor/laravel-quota/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/projectmentor/laravel-quota
[link-downloads]: https://packagist.org/packages/projectmentor/laravel-quota
[link-author]: https://github.com/projectmentor
[link-contributors]: ../../contributors
