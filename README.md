[![Build Status](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath.svg?branch=master)](https://travis-ci.org/cirrusidentity/simplesamlphp-module-authstormpath)
[![Coverage Status](https://coveralls.io/repos/github/cirrusidentity/simplesamlphp-module-authstormpath/badge.svg?branch=master)](https://coveralls.io/github/cirrusidentity/simplesamlphp-module-authstormpath?branch=master)
# simplesamlphp-module-authstormpath
SSP Authentication Module for stormpath

# Usage

## Install

The module is installable with composer.

```bash
composer config repositories.cirrus-authstormpath git https://github.com/cirrusidentity/simplesamlphp-module-authstormpath
composer require cirrusidentity/simplesamlphp-module-authstormpath:dev-master
```

## Configuration

In `authsources.php` configure the module
```php
$config = array(
    'stormpath' => array(
        'authstormpath:Stormpath',
        // The Stormpath application to use for authenticating
        'applicationHref' => 'https://api.stormpath.com/v1/applications/1TQNTuiFuXSzJGvaMHs4qI',
        // Stormpath API key file
        'apiKeyFileLocation' => '/path/to/stormpath.properties',
	// Optional account store to authenticate users against.
        // If not set, authentication happens against any account store configured for the application
        'accountStore' => 'https://api.stormpath.com/v1/organizations/3DuSeGAkNGZeOqewOy1fSP',
    ),
);
```

Provide the Stormpath api key properties file defined for `apiKeyFileLocation`

```
apiKey.id = JKJADF62JHH0HB234DF
apiKey.secret = JI23423SOMESECRETNJKNADFOIJ298U432
```

## Attributes

All user profile and custom data (excluding complex attributes) are mapped to SAML attributes. The attribute names will match the Stormpath names.
You will likely want to map these to OIDs or Ldap names, depending on your usecase.

In your `saml20-idp-hosted.php` file

```php
'authproc' => array(
            // Map Stormpath attributes
            150 => array(
                'class' => 'core:AttributeMap',
                // stormpath attributes
                'fullName' => 'urn:oid:2.16.840.1.113730.3.1.241',
                'email' => 'urn:oid:0.9.2342.19200300.100.1.3',
                'givenName' => 'urn:oid:2.5.4.42',
                'surname' => 'urn:oid:2.5.4.4',
            ),
            // Map any attributes that have multiple oids
            160 => array(
                'class' => 'core:AttributeMap',
                '%duplicate',
                //displayname => cn
                'urn:oid:2.16.840.1.113730.3.1.241' => 'urn:oid:2.5.4.3',
            ),
)
```


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

Some integration tests are performed against Stormpath APIs. You can run these by setting up a stormpath API key/secret and having certain users present in your stormpath tenant

### Travis CI

The `travis-secrets.tar.enc` file is encrypted for use by travis-ci. The build will decrypt and untar two files: `ssp-authstormpath-test.ini` and `apiKey-test.properties` 
that contain secret information for doing integrations with Stormpath. You can setup your own local version of those files, with your own free Stormpath tenant.

Occasionaly the tar file will need to be update.

```bash
tar czf travis-secrets.tar apiKey-test.properties ssp-authstormpath-test.ini 
travis encrypt-file travis-secrets.tar 
git add travis-secrets.tar.enc
git commit -m '...'

```

### Local Testing

You can setup your own instance of Stormpath to run integration tests.

```bash
cat ssp-authstormpath-test.ini; echo
# Username for a user to test authentication with
good_username=testusername
# Password used for testing authentication
good_password=pasSWORD
# Your application href
applicationHref=https://api.stormpath.com/v1/applications/43tLg9FaBMBOXqAhsCYXlb
# Where the credentials for the stormpath API are stored
apiKeyFileLocation=apiKey-test.properties
```

```bash
cat apiKey-test.properties
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
      "customNumber": 42,
      "customComplex" : {
        "subAttr" : "subValue",
        "subComplex" : {
          "turtles" : "allTheWay"
        },
        "subArray" : ["x","y"]
      }
  },
```