<?php

namespace RMS\PushNotificationsBundle\Message;

class AndroidMessage implements MessageInterface
{
	/**
	 * The data to send in the message
	 */
	protected $data = array();

    /**
     * Sets the data. For Android, this is the entire body of the message
     *
     * @param array $data The data to send
     */
	public function setData(array $data)
	{
		$this->data = $data;
	}

    /**
     * Gets the message body to send
     */
	public function getMessageBody()
	{
		return $this->data;
	}
}