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
     * Array for streams to APN
     *
     * @var array
     */
    protected $apnStreams;

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
        $this->apnStreams = array();
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

        $payload = $this->createPayload($message->getDeviceIdentifier(), $message->getMessageBody());
        $result = $this->writeApnStream($apnURL, $payload, strlen($payload));

        return $result;
    }

    /**
     * Write data to the apn stream that is associated with the given apn URL
     *
     * @param string $apnURL
     * @param string $string
     * @param int $length
     * @param bool $reconnectonerror
     * @throws \RuntimeException
     * @return int
     */
    protected function writeApnStream($apnURL, $string, $length, $reconnectonerror = true)
    {
        // Get the correct Apn stream and send data
        $fp = $this->getApnStream($apnURL);
        $result = @fwrite($fp, $string, $length);

        // Check if sending did succeed, if not retry if $reconnectonerror is set to true
        if ($result == false && $reconnectonerror) {
            $this->closeApnStream($apnURL);
            $result = $this->writeApnStream($apnURL, $string, $length, false);
        }

        return $result;
    }

    /**
     * Get an apn stream associated with the given apn URL, create one if necessary
     *
     * @param string $apnURL
     * @throws \RuntimeException
     * @return resource
     */
    protected function getApnStream($apnURL)
    {
        if (!isset($this->apnStreams[$apnURL])) {
            // No stream found, setup a new stream
            $ctx = $this->getStreamContext();
            $this->apnStreams[$apnURL] = stream_socket_client($apnURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
            if (!$this->apnStreams[$apnURL]) {
                throw new \RuntimeException("Couldn't connect to APN server");
            }
        }

        return $this->apnStreams[$apnURL];
    }

    /**
     * Close the apn stream associated with the given apn URL
     *
     * @param string $apnURL
     */
    protected function closeApnStream($apnURL)
    {
        if (isset($this->apnStreams[$apnURL])) {
            // Stream found, close the stream
            fclose($this->apnStreams[$apnURL]);
            unset($this->apnStreams[$apnURL]);
        }
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
        $token = preg_replace("/[^0-9A-Fa-f]/", "", $token);
        $payload = chr(0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
