<?php

namespace RMS\PushNotificationsBundle\Device\iOS;

class Feedback
{
    public $timestamp;
    public $tokenLength;
    public $uuid;

    /**
     * Unpacks the APNS data into the required fields
     *
     * @param $data
     * @return \RMS\PushNotificationsBundle\Device\iOS\Feedback
     */
    public function unpack($data)
    {
        $token = unpack("N1timestamp/n1length/H*token", $data);
        $this->timestamp = $token["timestamp"];
        $this->tokenLength = $token["length"];
        $this->uuid = $token["token"];

        return $this;
    }
}
