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

    protected $type;

    protected $title;

    protected $text;

    protected $image;


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
        return $this->text;
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

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * @param mixed $image
     */
    public function setImage($image)
    {
        $this->image = $image;
    }

    public function getNotificationClass()
    {
        return static::$notificationClass[$this->getType()];
    }
}
