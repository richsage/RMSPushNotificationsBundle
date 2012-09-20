RMSPushNotificationsBundle
=====================================

[![Build Status](https://secure.travis-ci.org/richsage/RMSPushNotificationsBundle.png)](http://travis-ci.org/richsage/RMSPushNotificationsBundle)

A bundle to allow sending of push notifications to mobile devices. 
Currently supports Android ([Google Cloud Messaging - GCM](http://developer.android.com/guide/google/gcm/index.html)) and iOS devices.

## Requirements

* Symfony 2.0.*
* Dependencies:
 * [`Buzz`](https://github.com/kriswallsmith/Buzz)

## Installation

### Using [Composer](https://github.com/composer/composer)

Add in your composer.json

```js
{
    "require": {
        "richsage/rms-push-notifications-bundle": "dev-master"
    }
}
```

### Install the bundle

``` bash
$ curl -s http://getcomposer.org/installer | php
$ php composer.phar update richsage/rms-push-notifications-bundle
```

Composer will install the bundle to your project's `vendor/bundles/RMS/PushNotificationBundle` directory.

### Using the vendors script

Add the following lines in your `deps` file:

``` ini
[PushNotificationsBundle]
    git=https://github.com/richsage/RMSPushNotificationsBundle
    target=bundles/RMS/PushNotificationsBundle
```

Now, run the vendors script to download the bundle:

``` bash
$ php bin/vendors install
```

### Using submodules

If you prefer instead to use git submodules, then run the following:

``` bash
$ git submodule add git://github.com/richsage/RMSPushNotificationsBundle.git vendor/bundles/RMS/PushNotificationsBundle
$ git submodule update --init
```

### Configure the Autoloader

Add the `RMS\\PushNotificationsBundle` namespace to your autoloader:

``` php
<?php
// app/autoload.php

$loader->registerNamespaces(array(
    // ...
    'RMS\\PushNotificationsBundle' => __DIR__.'/../vendor/bundles',
));
```

### Enable the bundle via the kernel

``` php
<?php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new RMS\PushNotificationsBundle\RMSPushNotificationsBundle(),
    );
}
```

## Configuration

### config.yml

```yaml
rms_push_notifications:
    android:
        api_key: "Your API Key (use the Browser key)"
    ios:
        sandbox: "Is Sandbox mode - false default"
        pem: "Path to the pem certificate"
        passphrase: "Password to your pem certyficate
```

TODO:
- add Usage to this README ;-)
- Windows Mobile messages
- Blackberry messages