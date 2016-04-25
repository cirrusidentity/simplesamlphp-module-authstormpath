[![Build Status](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath.svg?branch=master)](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath)

# simplesamlphp-module-authstormpath
SSP Authentication Module for stormpath

# Development

## PHP Version

Module targets php 5.6 and later. This is a requirement for some
version of our dependencies (we need phpunit > 5.2), and older version
have lost (or are about to lose) security support.  ## SSP Integration

## SSP Integration

For automated tests we need:
 * the test framework to find our classes and SSP's classes
 * SSP to find its necessary configuration files
 * SSP to resolve any module specific files.

The env variable `SIMPLESAMLPHP_CONFIG_DIR` is used to tell SSP where the test configuration files are.
SSP assumes certain files, like templates, will be in its `module` directory. The `bootstrap.php` symlinks the root of this project
into the composer installed SSP's module directory. This takes care of having the SSP autoloader find our classes and takes care of SSP
assuming certain files are installed relative to it.

## Style Guide

Code should conform to PSR-2. Exceptions are made for namespace and class names since SSP has its own autoloader and conventions.

```bash
phpcs --standard=PSR2 lib
```

## Testing

We make use of features found in later versions of phpunit. The version installed in vendor is compatible with our tests.

`vendor/bin/phpunit`
