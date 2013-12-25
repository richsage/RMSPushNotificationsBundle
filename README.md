# RMSPushNotificationsBundle ![](https://secure.travis-ci.org/richsage/RMSPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android (C2DM, GCM), Blackberry and iOS devices.

## Installation

To use this bundle in your Symfony2 project add the following to your `composer.json`:

    {
        "require": {
            // ...
            "richsage/rms-push-notifications-bundle": "dev-master"
        }
    }

and enable it in your kernel:

    <?php
    // app/AppKernel.php

    public function registerBundles()
    {
        $bundles = array(
            // ...
            new RMS\PushNotificationsBundle\RMSPushNotificationsBundle(),
        );
    }


## Configuration

Configuration options available are as follows. Note that the specific services will
only be available if you provide configuration respectively for them.

    rms_push_notifications:
      android:
          c2dm:
              username: <string_android_c2dm_username>
              password: <string_android_c2dm_password>
              source: <string_android_c2dm_source>
          gcm:
              api_key: <string_android_gcm_api_key>
              use_multi_curl: <boolean_android_gcm_use_multi_curl> # default is true
      ios:
          sandbox: <bool_use_apns_sandbox>
          pem: <path_apns_certificate> # can be absolute or relative path (from app directory)
          passphrase: <string_apns_certificate_passphrase>
      mac:
          sandbox: <bool_use_apns_sandbox>
          pem: <path_apns_certificate>
          passphrase: <string_apns_certificate_passphrase>
      blackberry:
          evaluation: <bool_bb_evaluation_mode>
          app_id: <string_bb_app_id>
          password: <string_bb_password>

NOTE: If you are using Windows, you may need to set the Android GCM `use_multi_curl` flag to false for GCM messages to be sent correctly.

## Usage

A little example of how to push your first message to an iOS device, we'll assume that you've set up the configuration correctly:

    use RMS\PushNotificationsBundle\Message\iOSMessage;

    class PushDemoController extends Controller
    {
        public function pushAction()
        {
            $message = new iOSMessage();
            $message->setMessage('Oh my! A push notification!');
            $message->setDeviceIdentifier('test012fasdf482asdfd63f6d7bc6d4293aedd5fb448fe505eb4asdfef8595a7');

            $this->container->get('rms_push_notifications')->send($message);

            return new Response('Push notification send!');
        }
    }

The send method will detect the type of message so if you'll pass it an `AndroidMessage` it will automatically send it through the C2DM/GCM servers, and likewise for Mac and Blackberry.

## Android messages

Since both C2DM and GCM are still available, the `AndroidMessage` class has a small flag on it to toggle which service to send it to.  Use as follows:

    use RMS\PushNotificationsBundle\Message\AndroidMessage;

    $message = new AndroidMessage();
    $message->setGCM(true);

to send as a GCM message rather than C2DM.

## iOS Feedback service

The Apple Push Notification service also exposes a Feedback service where you can get information about failed push notifications - see [here](https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/CommunicatingWIthAPS.html#//apple_ref/doc/uid/TP40008194-CH101-SW3) for further details.

This service is available within the bundle.  The following code demonstrates how you can retrieve data from the service:

    $feedbackService = $container->get("rms_push_notifications.ios.feedback");
    $uuids = $feedbackService->getDeviceUUIDs();

Here, `$uuids` contains an array of [Feedback](https://github.com/richsage/RMSPushNotificationsBundle/blob/master/Device/iOS/Feedback.php) objects, with timestamp, token length and the device UUID all populated.

Apple recommend you poll this service daily.
