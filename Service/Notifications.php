<?php

namespace RMS\PushNotificationsBundle\Service;

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
     * @param $osType
     * @param $deviceToken
     * @param $message
     * @throws \RuntimeException
     * @return bool
     */
    public function send($osType, $deviceToken, $message)
    {
        if (!isset($this->handlers[$osType])) {
            throw new \RuntimeException("OS type {$osType} not supported");
        }

        return $this->handlers[$osType]->send($deviceToken, $message);
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
}
