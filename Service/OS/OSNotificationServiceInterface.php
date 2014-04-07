<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use RMS\PushNotificationsBundle\Message\MessageInterface;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @return mixed
     */
    public function send(MessageInterface $message);
}
