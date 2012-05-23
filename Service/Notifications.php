<?php

namespace RMS\PushNotificationsBundle\Service;

class Notifications
{
    public $foo;

    const OS_ANDROID = "rms.push_notifications.os.android";
    const OS_IOS = "rms.push_notifications.os.ios";
    const OS_BLACKBERRY = "rms.push_notifications.os.blackberry";
    const OS_WINDOWSMOBILE = "rms.push_notifications.os.windowsmobile";

    public function __construct()
    {
    }

    /**
     * Sends a message to a device, identified by
     * the OS and the supplied device token
     *
     * @param $osType
     * @param $deviceToken
     * @param $message
     * @return bool
     */
    public function send($osType, $deviceToken, $message)
    {

    }
}
