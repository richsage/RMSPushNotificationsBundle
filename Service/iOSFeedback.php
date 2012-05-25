<?php

namespace RMS\PushNotificationsBundle\Service;

use RMS\PushNotificationsBundle\Device\iOS\Feedback;

class iOSFeedback
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
     * Gets an array of device UUID unregistration details
     * from the APN feedback service
     *
     * @throws \RuntimeException
     * @return array
     */
    public function getDeviceUUIDs()
    {
        if (!strlen($this->pem)) {
            throw new \RuntimeException("PEM not provided");
        }

        $feedbackURL = "ssl://feedback.push.apple.com:2196";
        $data = "";

        $ctx = $this->getStreamContext();
        $fp = stream_socket_client($feedbackURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        while (!feof($fp)) {
            $data .= fread($fp, 4096);
        }
        fclose($fp);
        if (!strlen($data)) {
            return array();
        }

        $feedbacks = array();
        $items = str_split($data, 40);
        foreach ($items as $item) {
            $feedback = new Feedback();
            $feedback->timestamp = substr($item, 0, 4);
            $feedback->tokenLength = substr($item, 4, 2);
            $feedback->uuid = substr($item, -32);
            $feedbacks[] = $feedback;
        }
        return $feedbacks;
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

}
