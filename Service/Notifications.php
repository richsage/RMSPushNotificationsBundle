<?php

namespace RMS\PushNotificationsBundle\Service;

use RMS\PushNotificationsBundle\Message\MessageInterface;

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
        if (!isset($this->handlers[$message->getTargetOS()])) {
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
}
