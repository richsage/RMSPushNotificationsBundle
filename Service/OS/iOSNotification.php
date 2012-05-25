<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Buzz\Browser;

class iOSNotification implements OSNotificationServiceInterface
{
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
     * @param $pem
     * @param $passphrase
     */
    public function __construct($pem, $passphrase)
    {
        $this->pem = $pem;
        $this->passphrase = $passphrase;
    }

    /**
     * Send a notification message
     *
     * @param $deviceToken
     * @param $message
     * @param string $messageType Unused for iOS push
     * @return bool
     * @throws \RuntimeException
     */
    public function send($deviceToken, $message, $messageType = null)
    {
        $apnURL = "ssl://gateway.push.apple.com:2195";
        $ctx = $this->getStreamContext();
        $fp = stream_socket_client($apnURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) {
            throw new \RuntimeException("Couldn't connect to APN server");
        }

        $payload = $this->createPayload($deviceToken, $message);
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
        $payloadBody = array(
            "aps" => array(
                "alert" => $message,
                "sound" => "default",
            ),
        );

        $jsonBody = json_encode($payloadBody);
        $payload = chr(0) . pack("n", 32) . pack("H*", $token) . pack("n", strlen($jsonBody)) . $jsonBody;

        return $payload;
    }
}
