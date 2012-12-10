# RMSPushNotificationsBundle ![](https://secure.travis-ci.org/richsage/RMSPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android (C2DM, GCM), Blackberry and iOS devices.

## Installation

To use this bundle in your Symfony2 project add `richsage/rms-push-notifications-bundle` to the required packages in your `composer.json` and run `php composer.phar update` to install the bundle. Then add `new RMS\PushNotificationsBundle\RMSPushNotificationsBundle()` to your `$bundles`-array in the `AppKernel.php` and you're ready!

## Configuration

Below you'll find all configuration options; just use what you need:

    rms_push_notifications:
      android:
          c2dm:
              username: <string_android_c2dm_username>
              password: <string_android_c2dm_password>
              source: <string_android_c2dm_source>
          gcm:
              api_key: <string_android_gcm_api_key>
      ios:
          sandbox: <bool_use_apns_sandbox>
          pem: <path_apns_certificate>
          passphrase: <string_apns_certificate_passphrase>
      blackberry:
          evaluation: <bool_bb_evaluation_mode>
          app_id: <string_bb_app_id>
          password: <string_bb_password>

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

The send method will detect the type of message so if you'll pass it an `AndroidMessage` it will automatically send it through the C2DM/GCM servers, and likewise for Blackberry.

## Android messages

Since both C2DM and GCM are still available, the `AndroidMessage` class has a small flag on it to toggle which service to send it to.  Use as follows:

    use RMS\PushNotificationsBundle\Message\AndroidMessage;

    $message = new AndroidMessage();
    $message->setGCM(true);
    
to send as a GCM message rather than C2DM.

