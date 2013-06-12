<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Message\AppleMessage;
use RMS\PushNotificationsBundle\Device\Types;

class MacMessage extends AppleMessage
{
    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return Types::OS_MAC;
    }
}
