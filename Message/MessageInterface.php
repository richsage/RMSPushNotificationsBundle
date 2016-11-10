<?php

namespace RMS\PushNotificationsBundle\Message;

interface MessageInterface
{
    public function setMessage($title, $message);

    public function setData($data);

    public function setDeviceIdentifier($identifier);

    public function getMessageBody();

    public function getDeviceIdentifier();

    public function getTargetOS();
}
