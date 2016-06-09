<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface;
use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\AppleMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface,
    Symfony\Component\Filesystem\Filesystem,
    RMS\PushNotificationsBundle\Service\EventListenerInterface;
use RMS\PushNotificationsBundle\Service\EventListener;

class AppleNotification implements OSNotificationServiceInterface, EventListenerInterface
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
    protected $pemPath;

    /**
     * Passphrase for PEM file
     *
     * @var string
     */
    protected $passphrase;

    /**
     * Content of PEM
     *
     * @var string
     */
    protected $pemContent;

    /**
     * Passphrase for PEM content
     *
     * @var string
     */
    protected $pemContentPassphrase;

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
     * Connection timeout
     *
     * @var int
     */
    protected $timeout;

    /**
     * Collection of the responses from the APN
     *
     * @var array
     */
    protected $responses = array();

    /**
     * Cache dir used for cache pem file
     *
     * @var string
     */
    protected $cachedir;

    /**
     * Monolog logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Cache pem filename
     */
    const APNS_CERTIFICATE_FILE = '/rms_push_notifications/apns.pem';

    /**
     * Status code retrieve when APNS server closed the connection
     */
    const APNS_SHUTDOWN_CODE = 10;

    /**
     * Constructor
     *
     * @param bool          $sandbox
     * @param string        $pem
     * @param string        $passphrase
     * @param bool          $jsonUnescapedUnicode
     * @param int           $timeout
     * @param string        $cachedir
     * @param EventListener $eventListener
     * @param LoggerInterface $logger
     */
    public function __construct($sandbox, $pem, $passphrase = "", $jsonUnescapedUnicode = FALSE, $timeout = 60, $cachedir = "", EventListener $eventListener = null, $logger = null)
    {
        $this->useSandbox = $sandbox;
        $this->pemPath = $pem;
        $this->passphrase = $passphrase;
        $this->apnStreams = array();
        $this->messages = array();
        $this->lastMessageId = -1;
        $this->jsonUnescapedUnicode = $jsonUnescapedUnicode;
        $this->timeout = $timeout;
        $this->cachedir = $cachedir;
        $this->logger = $logger;

        if ($eventListener != null) {
            $eventListener->addListener($this);
        }
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
     * Send a MDM or notification message
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface|\RMS\PushNotificationsBundle\Service\OS\MessageInterface $message
     * @throws \RuntimeException
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof AppleMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by APN", get_class($message)));
        }

        $apnURL = "ssl://gateway.push.apple.com:2195";
        if ($this->useSandbox) {
            $apnURL = "ssl://gateway.sandbox.push.apple.com:2195";
        }

        $messageId = ++$this->lastMessageId;

        if ($message->isMdmMessage()) {
            if ($message->getToken() == '') {
                throw new InvalidMessageTypeException(sprintf("Message type '%s' is a MDM message but 'token' is missing", get_class($message)));
            }

            if ($message->getPushMagicToken() == '') {
                throw new InvalidMessageTypeException(sprintf("Message type '%s' is a MDM message but 'pushMagicToken' is missing", get_class($message)));
            }

            $this->messages[$messageId] = $this->createMdmPayload($message->getToken(), $message->getPushMagicToken());
        } else {
            $this->messages[$messageId] = $this->createPayload($messageId, $message->getExpiry(), $message->getDeviceIdentifier(), $message->getMessageBody());
        }

        $errors = $this->sendMessages($messageId, $apnURL);

        return !$errors;
    }

    /**
     * Send all notification messages starting from the given ID
     *
     * @param  int                                                                $firstMessageId
     * @param  string                                                             $apnURL
     * @throws \RuntimeException
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return int
     */
    protected function sendMessages($firstMessageId, $apnURL)
    {
        $errors = array();
        // Loop through all messages starting from the given ID
        $messagesCount = count($this->messages);
        for ($currentMessageId = $firstMessageId; $currentMessageId < $messagesCount; $currentMessageId++) {
            // Send the message
            $result = $this->writeApnStream($apnURL, $this->messages[$currentMessageId]);

            // Check if there is an error result
            if (is_array($result)) {

                // Close the apn stream in case of Shutdown status code.
                if ($result['status'] === self::APNS_SHUTDOWN_CODE) {
                    $this->closeApnStream($apnURL);
                }

                $this->responses[] = $result;
                // Resend all messages that were sent after the failed message
                $this->sendMessages($result['identifier']+1, $apnURL);
                $errors[] = $result;
                if ($this->logger) {
                    $this->logger->error(json_encode($result));
                }
            } else {
                $this->responses[] = true;
            }
        }

        return $errors;
    }

    /**
     * Write data to the apn stream that is associated with the given apn URL
     *
     * @param  string            $apnURL
     * @param  string            $payload
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
        $streamsReadyToRead = @stream_select($readStreams, $null, $null, 1, 0);
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
     * @param  string            $apnURL
     * @throws \RuntimeException
     * @return resource
     */
    protected function getApnStream($apnURL)
    {
        if (!isset($this->apnStreams[$apnURL])) {
            // No stream found, setup a new stream
            $ctx = $this->getStreamContext();
            $this->apnStreams[$apnURL] = stream_socket_client($apnURL, $err, $errstr, $this->timeout, STREAM_CLIENT_CONNECT, $ctx);
            if (!$this->apnStreams[$apnURL]) {
                throw new \RuntimeException("Couldn't connect to APN server. Error no $err: $errstr");
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
        $pem = $this->pemPath;
        $passphrase = $this->passphrase;

        // Create cache pem file if needed
        if (!empty($this->pemContent)) {
            $filename = $this->cachedir . self::APNS_CERTIFICATE_FILE;

            $fs = new Filesystem();
            $fs->mkdir(dirname($filename));
            file_put_contents($filename, $this->pemContent);

            // Now we use this file as pem
            $pem = $filename;
            $passphrase = $this->pemContentPassphrase;
        }

        $ctx = stream_context_create();
        stream_context_set_option($ctx, "ssl", "local_cert", $pem);
        if (strlen($passphrase)) {
            stream_context_set_option($ctx, "ssl", "passphrase", $passphrase);
        }

        return $ctx;
    }

    /**
     * Creates the full payload for the notification
     *
     * @param int    $messageId
     * @param string $expiry
     * @param string $token
     * @param array  $message
     *
     * @return string
     *
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    protected function createPayload($messageId, $expiry, $token, $message)
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

            $encoding = mb_detect_encoding($message['aps']['alert']);
            if ($encoding != 'UTF-8' && $encoding != 'ASCII') {
                throw new \InvalidArgumentException(sprintf(
                    'Message must be UTF-8 encoding, "%s" given.',
                    mb_detect_encoding($message['aps']['alert'])
                ));
            }

            $jsonBody = json_encode($message, JSON_UNESCAPED_UNICODE);
        } else {
            $jsonBody = json_encode($message);
        }

        $token = preg_replace("/[^0-9A-Fa-f]/", "", $token);
        $payload = chr(1) . pack("N", $messageId) . pack("N", $expiry) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }

    /**
     * Creates a MDM payload
     *
     * @param string $token
     * @param string $magicPushToken
     *
     * @return string
     */
    public function createMdmPayload($token, $magicPushToken)
    {
        $jsonPayload = json_encode(array('mdm' => $magicPushToken));

        $payload = chr(0) . chr(0) . chr(32) . base64_decode($token) . chr(0)  . chr(strlen($jsonPayload)) . $jsonPayload;

        return $payload;
    }

    /**
     * Returns responses
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }

    /**
     * @param $pemContent
     * @param $passphrase
     */
    public function setPemAsString($pemContent, $passphrase) {
        if ($this->pemContent === $pemContent && $this->pemContentPassphrase === $passphrase) {
            return;
        }

        $this->pemContent = $pemContent;
        $this->pemContentPassphrase = $passphrase;

        // for new pem will take affect we need to close existing streams which use cached pem
        $this->closeStreams();
    }

    /**
     * @param string $pemPath
     * @param string $passphrase
     */
    public function setPemPath($pemPath, $passphrase)
    {
        if ($this->pemPath === $pemPath && $this->passphrase === $passphrase) {
            return;
        }

        $this->pemPath = $pemPath;
        $this->passphrase = $passphrase;

        // for new pem will take affect we need to close existing streams which use cached pem
        $this->closeStreams();
    }

    /**
     * Called on kernel terminate
     */
    public function onKernelTerminate()
    {
        $this->removeCachedPemFile();
        $this->closeStreams();
    }

    /**
     * Remove cache pem file
     */
    private function removeCachedPemFile()
    {
        $fs = new Filesystem();
        $filename = $this->cachedir . self::APNS_CERTIFICATE_FILE;
        if ($fs->exists(dirname($filename))) {
            $fs->remove(dirname($filename));
        }
    }

    /**
     * Close existing streams
     */
    private function closeStreams()
    {
        foreach ($this->apnStreams as $stream) {
            fclose($stream);
        }

        $this->apnStreams = [];
    }
}
