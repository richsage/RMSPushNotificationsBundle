<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class WindowsphoneMessage implements MessageInterface
{
    const TYPE_TOAST = 'toast';

    protected static $notificationClass = array(
        self::TYPE_TOAST => 2
    );

    protected $identifier;

    protected $text1 = '';

    protected $text2 = '';

    protected $target;

    public function __construct()
    {
        $this->target = self::TYPE_TOAST;
    }

    public function getTargetOS()
    {
        return Types::OS_WINDOWSPHONE;
    }

    public function getDeviceIdentifier()
    {
        return $this->identifier;
    }

    public function setDeviceIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    public function getMessageBody()
    {
        return array(
            'text1' => $this->text1,
            'text2' => $this->text2
        );
    }

    public function setMessage($message)
    {
        $this->text2 = $message;
    }

    public function setData($data)
    {
        // Not implemented yet
    }

    public function getTarget()
    {
        return $this->target;
    }

    public function getNotificationClass()
    {
        return static::$notificationClass[$this->getTarget()];
    }
}
