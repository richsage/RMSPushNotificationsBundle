<?php

namespace RMS\PushNotificationsBundle\Service\OS;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @abstract
     * @param $deviceToken
     * @param $message
     * @return mixed
     */
    public function send($deviceToken, $message);
}
