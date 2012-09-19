<?php

namespace RMS\PushNotificationsBundle\Tests\Message;

use RMS\PushNotificationsBundle\Device\Types,
    RMS\PushNotificationsBundle\Message\BlackberryMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;

class BlackberryMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $msg = new BlackberryMessage();
        $this->assertInstanceOf("RMS\PushNotificationsBundle\Message\MessageInterface", $msg);
        $this->assertEquals(Types::OS_BLACKBERRY, $msg->getTargetOS());
    }

    public function testDefaultBody()
    {
        $expected = null;
        $msg = new BlackberryMessage();
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testSettingBody()
    {
        $expected = "Foo";
        $msg = new BlackberryMessage();
        $msg->setMessage("Foo");
        $this->assertEquals($expected, $msg->getMessageBody());
    }
}
