<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class iOSMessage implements MessageInterface
{
    /**
     * Custom data for the APS body
     *
     * @var array
     */
    protected $customData = array();

    /**
     * Device identifier
     *
     * @var null
     */
    protected $identifier = null;

    /**
     * The APS core body
     *
     * @var array
     */
    protected $apsBody = array();

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->apsBody = array(
            "aps" => array(
            ),
        );
    }

    /**
     * Sets the message. For iOS, this is the APS alert message
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->apsBody["aps"]["alert"] = $message;
    }

    /**
     * Sets any custom data for the APS body
     *
     * @param $data
     */
    public function setData($data)
    {
        if (array_key_exists("aps", $data)) {
            unset($data["aps"]);
        }
        $this->customData = $data;
    }

    /**
     * Sets the identifier of the target device, eg UUID or similar
     *
     * @param $identifier
     */
    public function setDeviceIdentifier($identifier)
    {
        $this->identifier = $identifier;
    }

    /**
     * Gets the full message body to send to APN
     *
     * @return array
     */
    public function getMessageBody()
    {
        $payloadBody = $this->apsBody;
        if (!empty($this->customData)) {
            $payloadBody = array_merge($payloadBody, $this->customData);
        }
        return $payloadBody;
    }

    /**
     * Returns the device identifier
     *
     * @return null|string
     */
    public function getDeviceIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return Types::OS_IOS;
    }

    /**
     * iOS-specific
     * Sets the APS sound
     *
     * @param string $sound The sound to use. Use 'default' to use the built-in default
     */
    public function setAPSSound($sound)
    {
        $this->apsBody["aps"]["sound"] = $sound;
    }

    /**
     * iOS-specific
     * Sets the APS badge count
     *
     * @param string $badge The badge count to display
     */
    public function setAPSBadge($badge)
    {
        $this->apsBody["aps"]["badge"] = $badge;
    }
}
