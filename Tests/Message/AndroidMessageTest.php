<?php

namespace RMS\PushNotificationsBundle\Tests\Message;

use RMS\PushNotificationsBundle\Device\Types,
    RMS\PushNotificationsBundle\Message\AndroidMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;

class AndroidMessageTest extends \PHPUnit_Framework_TestCase
{
    public function testCreation()
    {
        $msg = new AndroidMessage();
        $this->assertInstanceOf("RMS\PushNotificationsBundle\Message\MessageInterface", $msg);
        $this->assertEquals(Types::OS_ANDROID_C2DM, $msg->getTargetOS());
    }

    public function testCoreBodyGeneratedOK()
    {
        $expected = array(
            "registration_id" => "",
            "collapse_key"    => AndroidMessage::DEFAULT_COLLAPSE_KEY,
            "data.message"    => "",
        );
        $msg = new AndroidMessage();
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testMessageAddedOK()
    {
        $expected = array(
            "registration_id" => "",
            "collapse_key"    => AndroidMessage::DEFAULT_COLLAPSE_KEY,
            "data.message"    => "Foo",
        );
        $msg = new AndroidMessage();
        $msg->setMessage("Foo");
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testNewCollapseKey()
    {
        $expected = array(
            "registration_id" => "",
            "collapse_key"    => 123,
            "data.message"    => "",
        );
        $msg = new AndroidMessage();
        $msg->setCollapseKey(123);
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testRegistrationIDAddedToBody()
    {
        $expected = array(
            "registration_id" => "ABC123",
            "collapse_key"    => AndroidMessage::DEFAULT_COLLAPSE_KEY,
            "data.message"    => "",
        );
        $msg = new AndroidMessage();
        $msg->setDeviceIdentifier("ABC123");
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testCustomData()
    {
        $expected = array(
            "registration_id" => "",
            "collapse_key"    => AndroidMessage::DEFAULT_COLLAPSE_KEY,
            "data.message"    => "",
            "custom"          => array("foo" => "bar"),
        );
        $msg = new AndroidMessage();
        $msg->setData(array("custom" => array("foo" => "bar")));
        $this->assertEquals($expected, $msg->getMessageBody());
    }

    public function testTypeChangesBasedOnGCM()
    {
        $msg = new AndroidMessage();
        $this->assertEquals(Types::OS_ANDROID_C2DM, $msg->getTargetOS());
        $msg->setGCM(true);
        $this->assertEquals(Types::OS_ANDROID_GCM, $msg->getTargetOS());
    }

    public function testSetIdentifierIsSingleEntryInGCMArray()
    {
        $msg = new AndroidMessage();
        $msg->setDeviceIdentifier("foo");
        $this->assertCount(1, $msg->getGCMIdentifiers());
    }

    public function testAddingGCMIdentifiers()
    {
        $msg = new AndroidMessage();
        $msg->addGCMIdentifier("foo");
        $msg->addGCMIdentifier("bar");
        $this->assertCount(2, $msg->getGCMIdentifiers());
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
