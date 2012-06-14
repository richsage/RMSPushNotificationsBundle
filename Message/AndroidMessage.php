<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class AndroidMessage implements MessageInterface
{
	/**
	 * The data to send in the message
     *
     * @var array
	 */
	protected $data = array();

    /**
     * Identifier of the target device
     *
     * @var string
     */
    protected $identifier = "";

    /**
     * Sets the data. For Android, this is the entire body of the message
     *
     * @param array $data The data to send
     */
	public function setData($data)
	{
		$this->data = (is_array($data) ? $data : array($data));
	}

    /**
     * Gets the message body to send
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
        return Types::OS_ANDROID;
    }
}
