[![Build Status](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath.svg?branch=master)](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath)
[![Coverage Status](https://coveralls.io/repos/github/cirrusidentity/simplesamlphp-module-authstormpath/badge.svg?branch=master)](https://coveralls.io/github/cirrusidentity/simplesamlphp-module-authstormpath?branch=master)
# simplesamlphp-module-authstormpath
SSP Authentication Module for stormpath

# Development

## PHP Version

Module targets php 5.6 and later. This is a requirement for some
version of our dependencies (we need phpunit > 5.2), and older version
have lost (or are about to lose) security support.

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

# Testing

We make use of features found in later versions of phpunit. The version installed in vendor is compatible with our tests.

`vendor/bin/phpunit`

## Stormpath Integration Tests

Some integration tests are performed against Stormpath APIs. You can run these by setting up a storm path API key/secret and having certain users.

```bash
cat ~/.stormpath/ssp-authstormpath-test.ini; echo
# Username for a user to test authentication with
good_username=testusername
# Password used for testing authentication
good_password=pasSWORD
# Your application href
applicationHref=https://api.stormpath.com/v1/applications/43tLg9FaBMBOXqAhsCYXlb
# Where the credentials for the stormpath API are stored
apiKeyFileLocation=/Users/user/.stormpath/apiKey.properties
```

```bash
cat ~/.stormpath/apiKey.properties
apiKey.id = 56adfZPBJEQWOBP0XTGADSAB6
apiKey.secret = qiibyVsZWzMXvt9Oi2Lt4Wnp+pv/G7mx4koa+gmFM
```

They will also assume certain users exist.

```bash
curl --user $STORMPATHCRED "https://api.stormpath.com/v1/accounts/1IcDYuodly2bgdK86rYO1?expand=customData" | jq '.'
  % Total    % Received % Xferd  Average Speed   Time    Time     Time  Current
                                 Dload  Upload   Total   Spent    Left  Speed
100  1659    0  1659    0     0   1001      0 --:--:--  0:00:01 --:--:--  1001
{
  "href": "https://api.stormpath.com/v1/accounts/1IcDYuodly2bgdK86rYO1",
  "username": "testusername",
  "email": "testuser@example.com",
  "givenName": "Test",
  "middleName": "",
  "surname": "User",
  "fullName": "Test User",
  "status": "ENABLED",
  "createdAt": "2016-05-04T20:58:50.533Z",
  "modifiedAt": "2016-07-28T16:49:54.327Z",
  "passwordModifiedAt": "2016-07-28T16:49:54.000Z",
  "emailVerificationToken": null,
  "customData": {
    "href": "https://api.stormpath.com/v1/accounts/1IcDYuodly2bgdK86rYO1/customData",
    "createdAt": "2016-05-04T20:58:50.533Z",
    "modifiedAt": "2016-08-22T20:30:30.215Z",
    "customAttribute": "4438ea02-5791-4fdb-983b-b35d69eb4c31",
     "customBoolean" : true,
      "customNull" : null,
      "customArray": ["a", "b", "c"],
      "customComplex" : {
        "subAttr" : "subValue",
        "subComplex" : {
          "turtles" : "allTheWay"
        },
        "subArray" : ["x","y"]
      }
  },
```