# Laravel Bare

_Component of `lifespikes/lifespikes`_

A very minimalist and customizable Laravel installation. Ideal for customizing Laravel path
resolutions and discovering service providers when working with a monorepo using
`symplify/monorepo-builder`

- [Getting Started](#installation)
    - [Installation](#installation)
- [Usage](#usage)
    - [Configuration](#customizing-directories)
    - [Registering Packages](#local-package-discovery)
    - [Building Apps on Bare](#building-apps-on-bare)
- [FAQ](#faq)
    - [Where is the [X] directory?](#where-is-the-x-directory)
    - [Why should I use Bare?](#why-go-bare)

## Installation

Setting up Bare is a bit different from a regular Laravel installation:

- No boilerplate/skeleton code
- Purely service-provider driven
- Bootstrap and other startup code is not included

You'll need to start by of course, installing the module:

`composer require @lifespikes/laravel-bare`

Then, your entrypoint needs to be set up. You will need to create an entrypoint
for **artisan** and another one for your **web app** if applicable:

```php
#!/usr/bin/env php
<?php

/*
 * This takes care of bootstrapping the application
 * and its paths. While it might look different, it's still
 * the same as a regular Laravel installation in terms
 * of behavior. Laravel Bare just implements Laravel
 * original scaffold differently, but the framework itself
 * is kept untouched.
 */
 
use function LifeSpikes\LaravelBare\Bootstrap\bare;

/* Define before any operations take place, for accuracy */
define('LARAVEL_START', microtime(true));

/* Composer autoloader */
require __DIR__ . '/vendor/autoload.php';

/* Specify the root dir and bootstrap */
bare(__DIR__)->artisan();
```

Setting up your web entrypoint is essentially the same:

```php
<?php

/* Assuming we're in public/index.php */

use function LifeSpikes\LaravelBare\Bootstrap\bare;

define('LARAVEL_START', microtime(true));
require __DIR__.'/../vendor/autoload.php';

/* Go up a level since this is usually in a subdirectory */
bare(__DIR__ . '/../')->web();
```

## Usage

There's no special configuration or usage for Bare besides its path
configuration and `monorepo-builder` support.

### Customizing Directories

The `bare()` bootstrap helper takes a base path as its first argument. However,
you can also pass an array of paths that Laravel will use as values when trying
to resolve against path instances like `resource_path`, `database_path`, etc.

```php
bare([
    'base' => __DIR__, /* Root Dir */
    
    /* Everything else solves relative to base */
    'public' => 'static',
    'cache'  => 'storage/cache',
    ...
])->web();
```

Defaults can be found in the `config/paths.php` file.

### Local Package Discovery

If you have built Laravel packages in the past, you're probably familiar
with the `{"extra": {"laravel": {...}}}` section of your composer.json file.

However, using this section in your standard, root-level composer.json file
will have no effect, since Laravel reads off your `vendor/composer/installed.json`
manifest when performing package discovery.

Bare adds a bit of logic during registration to allow local packages like
these to be discovered without having to manually register them against the
container instance.

This is especially helpful when building monorepo where you may have multiple
Laravel packages you wish to isolate.

[Read more about package discovery.](https://laravel.com/docs/9.x/packages#package-discovery)

### Building Apps on Bare

Bare is meant to be used as a base for building package-driven SOLID codebases.
This means you'll have to write your own service providers and bootstrap code as
explained earlier. Primarily, for example, we use bare as a simple way for us
to write modularized, independent packages that make up a large app. The Laravel
documentation is a great place to start:

https://laravel.com/docs/9.x/packages

## FAQ

### Where is the [X] directory?

Bare allows you to use Laravel as a library. It contains all the default
configurations found in the `laravel/laravel` scaffold, but removes all dependencies
in the `App\` namespace.

You'll need to use service providers to manually bind config, events, listeners, etc.
to your module.

If you're wondering why we did this, read the section below.

### Why go Bare?

When people think about Laravel, they usually think of two things:

- A web framework
- The `laravel/laravel` repository

Most people are unaware that these terms are exclusive of each other. The
components that make up the framework are actually contained in the
`laravel/framework` repository.

Libraries have historically been published by the maintainer, and then
implemented by the developer. Maintainer code is not present in the app's
codebase. This pattern is often seen in low-level languages like C, C++,
ASM, etc. _(OpenSSL, SDL, Vulkan, OpenGL, are good examples)_

This project is intended for developers with an architectural background
looking to harness the power of Laravel, while maintaining the freedom to
implement their own structures and patterns.
