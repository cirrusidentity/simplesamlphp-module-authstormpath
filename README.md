# simplesamlphp-module-authstormpath
SSP Authentication Module for stormpath

# Development

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

### Unit Tests

`phpunit`