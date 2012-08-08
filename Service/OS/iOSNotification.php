<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\iOSMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser;

class iOSNotification implements OSNotificationServiceInterface
{
    /**
     * Whether or not to use the sandbox APNS
     *
     * @var bool
     */
    protected $useSandbox = false;

    /**
     * Path to PEM file
     *
     * @var string
     */
    protected $pem;

    /**
     * Passphrase for PEM file
     *
     * @var string
     */
    protected $passphrase;

    /**
     * Constructor
     *
     * @param $sandbox
     * @param $pem
     * @param $passphrase
     */
    public function __construct($sandbox, $pem, $passphrase = "")
    {
        $this->useSandbox = $sandbox;
        $this->pem = $pem;
        $this->passphrase = $passphrase;
    }

    /**
     * Send a notification message
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface|\RMS\PushNotificationsBundle\Service\OS\MessageInterface $message
     * @throws \RuntimeException
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof iOSMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by APN", get_class($message)));
        }

        $apnURL = "ssl://gateway.push.apple.com:2195";
        if ($this->useSandbox) {
            $apnURL = "ssl://gateway.sandbox.push.apple.com:2195";
        }
        $ctx = $this->getStreamContext();
        $fp = stream_socket_client($apnURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) {
            throw new \RuntimeException("Couldn't connect to APN server");
        }

        $payload = $this->createPayload($message->getDeviceIdentifier(), $message->getMessageBody());
        $result = fwrite($fp, $payload, strlen($payload));
        fclose($fp);
        return $result;
    }

    /**
     * Gets a stream context set up for SSL
     * using our PEM file and passphrase
     *
     * @return resource
     */
    protected function getStreamContext()
    {
        $ctx = stream_context_create();

        stream_context_set_option($ctx, "ssl", "local_cert", $this->pem);
        if (strlen($this->passphrase)) {
            stream_context_set_option($ctx, "ssl", "passphrase", $this->passphrase);
        }

        return $ctx;
    }

    /**
     * Creates the full payload for the notification
     *
     * @param $token
     * @param $message
     * @return string
     */
    protected function createPayload($token, $message)
    {
        $jsonBody = json_encode($message, JSON_FORCE_OBJECT);
        $payload = chr(0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
