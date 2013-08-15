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
     * Queues a message for sending to a device, identified by
     * the OS and the supplied device token
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @throws \RuntimeException
     * @return bool
     */
    public function queue(MessageInterface $message)
    {
        if (!isset($this->handlers[$message->getTargetOS()])) {
            throw new \RuntimeException("OS type {$message->getTargetOS()} not supported");
        }

        $handler = $this->handlers[$message->getTargetOS()];

        if (method_exists($handler, "queue")) {
            return $handler->queue($message);
        } else {
            // Fall back to sending now if bulk messaging not supported
            return $handler->send($message);
        }
    }

    /**
     * Sends a message to a device instantly, identified by
     * the OS and the supplied device token
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface $message
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
     * Flush - send queued messages in all handlers
     *
     * @return array An array of errors returned by each handler, keyed by the OS.
     */
    public function flush()
    {
        $errors = array();
        foreach ($this->handlers as $osType => $handler) {
            if (method_exists($handler, "flush")) {
                // This is a no-op for platforms that don't support bulk sending
                $osErrors = $handler->flush();
                if (count($osErrors)) {
                    $errors[$osType] = $osErrors;
                }
            }
        }
        return $errors;
    }

    /**
     * Adds a handler
     *
     * @param $osType
     * @param $service
     */
    public function addHandler($osType, OS\OSNotificationServiceInterface $service)
    {
        if (!isset($this->handlers[$osType])) {
            $this->handlers[$osType] = $service;
        }
    }
}
