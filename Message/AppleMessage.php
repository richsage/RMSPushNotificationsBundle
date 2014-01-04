<?php

namespace RMS\PushNotificationsBundle\Message;

class AppleMessage implements MessageInterface
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
     * Expiration date (UTC)
     *
     * A fixed UNIX epoch date expressed in seconds (UTC) that identifies when the notification is no longer valid and can be discarded.
     * If the expiry value is non-zero, APNs tries to deliver the notification at least once.
     * Specify zero to request that APNs not store the notification at all.
     *
     * @var int
     */
    protected $expiry = 0;

    /**
     * Class constructor
     */
    public function __construct($identifier = NULL)
    {
        $this->apsBody = array(
            "aps" => array(
            ),
        );

        if ($identifier !== NULL) {
            $this->identifier = $identifier;
        }
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
     * @param array $data
     */
    public function setData($data)
    {
        if (!is_array($data)) {
            throw new \InvalidArgumentException(sprintf('Messages custom data must be array, "%s" given.', gettype($data)));
        }

        if (array_key_exists("aps", $data)) {
            unset($data["aps"]);
        }

        foreach ($data as $key => $value) {
            $this->addCustomData($key, $value);
        }

        return $this;
    }

    /**
     * Add custom data
     *
     * @param string $key
     * @param mixed $value
     */
    public function addCustomData($key, $value)
    {
        if ($key == 'aps') {
            throw new \LogicException('Can\'t replace "aps" data. Please call to setMessage, if your want replace message text.');
        }

        if (is_object($value)) {
            if (interface_exists('JsonSerializable') && !$value instanceof \stdClass && !$value instanceof \JsonSerializable) {
                throw new \InvalidArgumentException(sprintf(
                    'Object %s::%s must be implements JsonSerializable interface for next serialize data.',
                    get_class($value), spl_object_hash($value)
                ));
            }
        }

        $this->customData[$key] = $value;
        return $this;
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
     * Must be implemented by subclass
     *
     * @return string
     */
    public function getTargetOS()
    {
        return "";
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
    
    /**
     * iOS-specific
     * Sets the APS content available flag, used to transform the notification into remote-notification
     * and trigger the "didReceiveRemoteNotification: fetchCompletionHandler:" method on iOS apps
     *
     * @param string $contentAvailable The flag to set the content-available option, for example set it to 1.
     */
    public function setAPSContentAvailable($contentAvailable)
    {
        $this->apsBody["aps"]["content-available"] = $contentAvailable;
    }

    /**
     * Set expiry of message
     *
     * @param int $expiry
     */
    public function setExpiry($expiry)
    {
        $this->expiry = $expiry;
    }

    /**
     * Get expiry of message
     *
     * @return int
     */
    public function getExpiry()
    {
        return $this->expiry;
    }
}
