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
     * @param string $messageType
     * @return mixed
     */
    public function send($deviceToken, $message, $messageType = null);
}
