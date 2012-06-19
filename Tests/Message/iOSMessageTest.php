<?php

namespace RMS\PushNotificationsBundle\Tests\Message;

use RMS\PushNotificationsBundle\Device\Types,
    RMS\PushNotificationsBundle\Message\iOSMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;

class iOSMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $msg = new iOSMessage();
        $this->assertInstanceOf("RMS\PushNotificationsBundle\Message\MessageInterface", $msg);
        $this->assertEquals(Types::OS_IOS, $msg->getTargetOS());
    }

    public function testCoreBodyGeneratedOK()
    {
        $expected = array(
            "aps" => array(),
        );
        $msg = new iOSMessage();
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testAPSAlertAddedOK()
    {
        $expected = array(
            "aps" => array(
                "alert" => "Foo",
            ),
        );
        $msg = new iOSMessage();
        $msg->setMessage("Foo");
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testAPSBadgeAddedOK()
    {
        $expected = array(
            "aps" => array(
                "badge" => 1,
            ),
        );
        $msg = new iOSMessage();
        $msg->setAPSBadge(1);
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testAPSSoundAddedOK()
    {
        $expected = array(
            "aps" => array(
                "sound" => "default",
            ),
        );
        $msg = new iOSMessage();
        $msg->setAPSSound("default");
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testCustomDataAddedOK()
    {
        $expected = array(
            "aps" => array(),
            "custom" => array("foo" => "bar"),
        );
        $msg = new iOSMessage();
        $msg->setData(array("custom" => array("foo" => "bar")));
        $this->assertEquals($expected, $msg->getMessageBody());
    }
}
