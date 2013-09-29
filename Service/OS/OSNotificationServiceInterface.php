<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface;
use RMS\PushNotificationsBundle\Message\MessageInterface;

interface OSNotificationServiceInterface
{
    /**
     * Send a notification message
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @return mixed
     */
    public function send(MessageInterface $message);

    /**
     * Inject a logger
     *
     * @param LoggerInterface $logger
     * @return mixed
     */
    public function setLogger(LoggerInterface $logger);
}
