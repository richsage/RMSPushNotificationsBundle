<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class BlackberryMessage implements MessageInterface
{
    /**
     * The data to send in the message
     *
     * @var mixed
     */
    protected $data = null;

    /**
     * Identifier of the target device
     *
     * @var string
     */
    protected $identifier = "";

    /**
     * Sets the message
     * For Blackberry, this is the same as the data
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->setData($message);
    }

    /**
     * Sets the data. For Blackberry, this is any data required
     *
     * @param array $data The custom data to send
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Gets the message body to send
     * For Blackberry, this is just our data object
     *
     * @return array
     */
    public function getMessageBody()
    {
        return $this->data;
    }

    /**
     * Sets the identifier of the target device, eg UUID or similar
     *
     * @param $identifier
     */
    public function setDeviceIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return Types::OS_BLACKBERRY;
    }

    /**
     * Returns the target device identifier
     *
     * @return string
     */
    public function getDeviceIdentifier()
    {
        return $this->identifier;
    }
}
