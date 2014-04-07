<?php

namespace RMS\PushNotificationsBundle\Service;

use RMS\PushNotificationsBundle\Device\iOS\Feedback;

class iOSFeedback
{
    /**
     * Sandbox mode or not
     *
     * @var bool
     */
    protected $sandbox;

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
    public function __construct($sandbox, $pem, $passphrase)
    {
        $this->sandbox = $sandbox;
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
        if ($this->sandbox) {
            $feedbackURL = "ssl://feedback.sandbox.push.apple.com:2196";
        }
        $data = "";

        $ctx = $this->getStreamContext();
        $fp = stream_socket_client($feedbackURL, $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
        if (!$fp) {
            throw new \RuntimeException("Couldn't connect to APNS Feedback service. Error no $err: $errstr");
        }
        while (!feof($fp)) {
            $data .= fread($fp, 4096);
        }
        fclose($fp);
        if (!strlen($data)) {
            return array();
        }

        $feedbacks = array();
        $items = str_split($data, 38);
        foreach ($items as $item) {
            $feedback = new Feedback();
            $feedbacks[] = $feedback->unpack($item);
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
