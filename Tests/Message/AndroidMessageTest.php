<?php

namespace RMS\PushNotificationsBundle\Tests\Message;

use RMS\PushNotificationsBundle\Device\Types,
    RMS\PushNotificationsBundle\Message\AndroidMessage;

class AndroidMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $msg = new AndroidMessage();
        $this->assertInstanceOf("RMS\\PushNotificationsBundle\\Message\\MessageInterface", $msg);
        $this->assertEquals(Types::OS_ANDROID_GCM, $msg->getTargetOS());
    }

    public function testSetIdentifierIsSingleEntryInGCMArray()
    {
        $msg = new AndroidMessage();
        $msg->setDeviceIdentifier("foo");
        $this->assertCount(1, $msg->getIdentifiers());
    }

    public function testAddingGCMIdentifiers()
    {
        $msg = new AndroidMessage();
        $msg->addIdentifier("foo");
        $msg->addIdentifier("bar");
        $this->assertCount(2, $msg->getIdentifiers());
    }

    public function testSetMessageIsReturnedInGetData()
    {
        $msg = new AndroidMessage();
        $message = 'Test message';
        $msg->setMessage($message);
        $this->assertEquals(array('message' => $message), $msg->getData());

        $msg->setData(array('id' => 10));
        $this->assertEquals(array('id' => 10, 'message' => $message), $msg->getData());

        $msg->setData(array('message' => 'Other message'));
        $this->assertEquals(array('message' => 'Other message'), $msg->getData());
    }
}
