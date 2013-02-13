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
     * Array for messages to APN
     *
     * @var array
     */
    protected $messages;

    /**
     * Last used message ID
     *
     * @var int
     */
    protected $lastMessageId;

    /**
     * JSON_UNESCAPED_UNICODE
     *
     * @var boolean
     */
    protected $jsonUnescapedUnicode = FALSE;

    /**
     * Constructor
     *
     * @param $sandbox
     * @param $pem
     * @param $passphrase
     */
    public function __construct($sandbox, $pem, $passphrase = "", $jsonUnescapedUnicode = FALSE)
    {
        $this->useSandbox = $sandbox;
        $this->pem = $pem;
        $this->passphrase = $passphrase;
        $this->apnStreams = array();
        $this->messages = array();
        $this->lastMessageId = -1;
        $this->jsonUnescapedUnicode = $jsonUnescapedUnicode;
    }

    /**
     * Set option JSON_UNESCAPED_UNICODE to json encoders
     *
     * @param boolean $jsonUnescapedUnicode
     */
    public function setJsonUnescapedUnicode($jsonUnescapedUnicode)
    {
        $this->jsonUnescapedUnicode = (bool) $jsonUnescapedUnicode;
        return $this;
    }

    /**
     * Send a notification message
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface|\RMS\PushNotificationsBundle\Service\OS\MessageInterface $message
     * @throws \RuntimeException
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return int
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

        $messageId = ++$this->lastMessageId;
        $this->messages[$messageId] = $this->createPayload($messageId, $message->getDeviceIdentifier(), $message->getMessageBody());
        $this->sendMessages($messageId, $apnURL);

        return $messageId;
    }

    /**
     * Send all notification messages starting from the given ID
     *
     * @param int $firstMessageId
     * @param string $apnURL
     * @throws \RuntimeException
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return int
     */
    protected function sendMessages($firstMessageId, $apnURL)
    {
        // Loop through all messages starting from the given ID
        for ($currentMessageId = $firstMessageId; $currentMessageId < count($this->messages); $currentMessageId++)
        {
            // Send the message
            $result = $this->writeApnStream($apnURL, $this->messages[$currentMessageId]);

            // Check if there is an error result
            if (is_array($result)) {
                // Resend all messages that where send after the failed message
                $this->sendMessages($result['identifier']+1, $apnURL);
            }
        }
    }

    /**
     * Write data to the apn stream that is associated with the given apn URL
     *
     * @param string $apnURL
     * @param string $payload
     * @throws \RuntimeException
     * @return mixed
     */
    protected function writeApnStream($apnURL, $payload)
    {
        // Get the correct Apn stream and send data
        $fp = $this->getApnStream($apnURL);
        $response = (strlen($payload) === @fwrite($fp, $payload, strlen($payload)));

        // Check if there is responsedata to read
        $readStreams = array($fp);
        $null = NULL;
        $streamsReadyToRead = stream_select($readStreams, $null, $null, 1, 0);
        if ($streamsReadyToRead > 0) {
            // Unpack error response data and set as the result
            $response = @unpack("Ccommand/Cstatus/Nidentifier", fread($fp, 6));
            $this->closeApnStream($apnURL);
        }

        // Will contain true if writing succeeded and no error is returned yet
        return $response;
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
            $this->apnStreams[$apnURL] = stream_socket_client($apnURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT, $ctx);
            if (!$this->apnStreams[$apnURL]) {
                throw new \RuntimeException("Couldn't connect to APN server");
            }

            // Reduce buffering and blocking
            if (function_exists("stream_set_read_buffer")) {
                stream_set_read_buffer($this->apnStreams[$apnURL], 6);
            }
            stream_set_write_buffer($this->apnStreams[$apnURL], 0);
            stream_set_blocking($this->apnStreams[$apnURL], 0);
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
     * @param $messageId
     * @param $token
     * @param $message
     * @return string
     */
    protected function createPayload($messageId, $token, $message)
    {
        if ($this->jsonUnescapedUnicode) {
            // Validate PHP version
            if (!version_compare(PHP_VERSION, '5.4.0', '>=')) {
                throw new \LogicException(sprintf(
                    'Can\'t use JSON_UNESCAPED_UNICODE option on PHP %s. Support PHP >= 5.4.0',
                    PHP_VERSION
                ));
            }

            // WARNING:
            // Set otpion JSON_UNESCAPED_UNICODE is violation
            // of RFC 4627
            // Because required validate charsets (Must be UTF-8)

            if (mb_detect_encoding($message['aps']['alert']) != 'UTF-8') {
                throw new \InvalidArgumentException(sprintf(
                    'Message must be UTF-8 encoding, "%s" given.',
                    mb_detect_encoding($message)
                ));
            }


            $jsonBody = json_encode($message, JSON_UNESCAPED_UNICODE ^ JSON_FORCE_OBJECT);
        }
        else {
            $jsonBody = json_encode($message, JSON_FORCE_OBJECT);
        }

        $token = preg_replace("/[^0-9A-Fa-f]/", "", $token);
        $payload = chr(1) . pack("N", $messageId) . pack("N", 0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
