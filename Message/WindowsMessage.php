<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class WindowsMessage implements MessageInterface
{
    const TYPE_TOAST = 'wns/toast';
    const TYPE_BADGE = 'wns/badge';
    const TYPE_TILE = 'wns/tile';
    const TYPE_RAW = 'wns/raw';

    protected static $notificationClass = array(
        self::TYPE_TOAST => 2
    );

    protected $identifier;

    protected $text1 = '';

    protected $text2 = '';

    protected $type;

    public function __construct()
    {
        $this->type = self::TYPE_TOAST;
    }

    public function getTargetOS()
    {
        return Types::OS_WINDOWS;
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

    public function getType()
    {
        return $this->type;
    }

    public function getNotificationClass()
    {
        return static::$notificationClass[$this->getType()];
    }
}
