<?php

namespace RMS\PushNotificationsBundle\Message;

interface MessageInterface
{
    public function setMessage($message);

    public function setData($data);

    public function getTargetOS();
}
