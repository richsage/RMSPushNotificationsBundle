<?php

namespace RMS\PushNotificationsBundle\Message;

interface MessageInterface
{
	public function setData($data);

    public function setDeviceIdentifier($identifier);

	public function getMessageBody();

    public function getTargetOS();
}
