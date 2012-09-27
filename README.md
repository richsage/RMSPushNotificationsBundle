# RMSPushNotificationsBundle ![](https://secure.travis-ci.org/richsage/RMSPushNotificationsBundle.png)

A bundle to allow sending of push notifications to mobile devices.  Currently supports Android and iOS devices.

## Installation

To use this bundle in your Symfony2 project add `richsage/rms-push-notifications-bundle` to the required packages in your `composer.json` and run `php composer.phar update` to install the bundle. Then add `new RMS\PushNotificationsBundle\RMSPushNotificationsBundle()` to your `$bundles`-array in the `AppKernel.php` and you're ready!

## Configuration

Below you find all configuration options, just use what you need:

    rms_push_notifications:
	    android:
	        c2dm:
	            username: <string_android_c2dm_username>
	            password: <string_android_c2dm_password>
	            source: <string_android_s2dm_source>
	        gcm:
	            api_key: <string_android_gcm_api_key>
	    ios:
	        sandbox: <bool_use_apns_sandbox>
	        pem: <path_apns_certificate>
	        passphrase: <string_apns_certificate_passphrase>
	    blackberry:
	        evaluation: <string_bb_evaluation>
	        app_id: <string_bb_app_ic>
	        password: <string_bb_password>

## Usage

A little example of how to push your first message to an iOS device, we'll assume that you've set up the configuration correctly:

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

The send method will detect the type of message so if you'll pass it an `AndroidMessage` it will automaticly send it through the c2dm/gcm servers.