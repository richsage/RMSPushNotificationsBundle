<?php

namespace RMS\PushNotificationsBundle\Message;

interface MessageInterface
{
	public function setData();

	public function getMessageBody();
}