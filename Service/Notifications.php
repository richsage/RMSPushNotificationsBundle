<?php

namespace RMS\PushNotificationsBundle\Service;

use RMS\PushNotificationsBundle\Device\Types;
use RMS\PushNotificationsBundle\Message\MessageInterface;
use RMS\PushNotificationsBundle\Service\OS\AppleNotification;

class Notifications
{
    /**
     * Array of handlers
     *
     * @var array
     */
    protected $handlers = array();

    /**
     * Constructor
     */
    public function __construct()
    {
    }

    /**
     * Sends a message to a device, identified by
     * the OS and the supplied device token
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @throws \RuntimeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$this->supports($message->getTargetOS())) {
            throw new \RuntimeException("OS type {$message->getTargetOS()} not supported");
        }
        return $this->handlers[$message->getTargetOS()]->send($message);
    }

    /**
     * Adds a handler
     *
     * @param $osType
     * @param $service
     */
    public function addHandler($osType, $service)
    {
        if (!isset($this->handlers[$osType])) {
            $this->handlers[$osType] = $service;
        }
    }

    /**
     * Get responses from handler
     *
     * @param  string            $osType
     * @return array
     * @throws \RuntimeException
     */
    public function getResponses($osType)
    {
        if (!isset($this->handlers[$osType])) {
            throw new \RuntimeException("OS type {$osType} not supported");
        }

        if (!method_exists($this->handlers[$osType], 'getResponses')) {
            throw new \RuntimeException("Handler for OS type {$osType} not supported getResponses() method");
        }

        return $this->handlers[$osType]->getResponses();
    }

    /**
     * Check if target OS is supported
     *
     * @param $targetOS
     *
     * @return bool
     */
    public function supports($targetOS)
    {
        return isset($this->handlers[$targetOS]);
    }


    /**
     * Set Apple Push Notification Service pem as string.
     * Service won't use pem file passed by config anymore.
     *
     * @param $pemContent string
     * @param $passphrase
     */
    public function setAPNSPemAsString($pemContent, $passphrase) {
        if (isset($this->handlers[Types::OS_IOS]) && $this->handlers[Types::OS_IOS] instanceof AppleNotification) {
            /** @var AppleNotification $appleNotification */
            $appleNotification = $this->handlers[Types::OS_IOS];
            $appleNotification->setPemAsString($pemContent, $passphrase);
        }
    }
}
