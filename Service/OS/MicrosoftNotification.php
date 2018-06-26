<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface;
use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException;
use RMS\PushNotificationsBundle\Message\WindowsphoneMessage;
use RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser,
    Buzz\Client\Curl;

class MicrosoftNotification implements OSNotificationServiceInterface
{
    /**
     * Browser object
     *
     * @var \Buzz\Browser
     */
    protected $browser;

    /**
     * Monolog logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param $timeout
     * @param $logger
     */
    public function __construct($timeout, $logger)
    {
        $options = array(
            'timeout' => $timeout,
            'verify' => false,
        );
        $this->browser = new Browser(new Curl($options));
        $this->logger = $logger;
    }

    public function send(MessageInterface $message)
    {
        if (!$message instanceof WindowsphoneMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by MPNS", get_class($message)));
        }

        $headers = array(
            'Content-Type: text/xml',
            'X-WindowsPhone-Target: ' . $message->getTarget(),
            'X-NotificationClass: ' . $message->getNotificationClass()
        );

        $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="utf-8"?><wp:Notification xmlns:wp="WPNotification" />');

        $msgBody = $message->getMessageBody();

        if ($message->getTarget() == WindowsphoneMessage::TYPE_TOAST) {
            $toast = $xml->addChild('wp:Toast');
            $toast->addChild('wp:Text1', htmlspecialchars($msgBody['text1'], ENT_XML1|ENT_QUOTES));
            $toast->addChild('wp:Text2', htmlspecialchars($msgBody['text2'], ENT_XML1|ENT_QUOTES));
        }

        $response = $this->browser->post($message->getDeviceIdentifier(), $headers, $xml->asXML());

        if ($response->getStatusCode() < 200 || $response->getStatusCode() >= 300) {
            $this->logger->error($response->getStatusCode(). ' : '. $response->getReasonPhrase());

            return false;
        }

        return true;
    }
}
