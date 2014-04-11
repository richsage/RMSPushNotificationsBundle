<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class AndroidMessage implements MessageInterface
{
    const DEFAULT_COLLAPSE_KEY = 1;

    /**
     * String message
     *
     * @var string
     */
    protected $message = "";

    /**
     * The data to send in the message
     *
     * @var array
     */
    protected $data = array();

    /**
     * Identifier of the target device
     *
     * @var string
     */
    protected $identifier = "";

    /**
     * Collapse key for data
     *
     * @var int
     */
    protected $collapseKey = self::DEFAULT_COLLAPSE_KEY;

    /**
     * Whether this is a GCM message
     *
     * @var bool
     */
    protected $isGCM = false;

    /**
     * A collection of device identifiers that the message
     * is intended for. GCM use only
     *
     * @var array
     */
    protected $allIdentifiers = array();

    /**
     * Options for GCM messages
     *
     * @var array
     */
    protected $gcmOptions = array();

    /**
     * Sets the string message
     *
     * @param $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * Returns the string message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Sets the data. For Android, this is any custom data to use
     *
     * @param array $data The custom data to send
     */
    public function setData($data)
    {
        $this->data = (is_array($data) ? $data : array($data));
    }

    /**
     * Returns any custom data
     *
     * @return array
     */
    public function getData()
    {
        return array_merge(array('message' => $this->getMessage()), $this->data);
    }

    /**
     * Gets the message body to send
     * This is primarily used in C2DM
     *
     * @return array
     */
    public function getMessageBody()
    {
        $data = array(
            "registration_id" => $this->identifier,
            "collapse_key"    => $this->collapseKey,
            "data.message"    => $this->message,
        );
        if (!empty($this->data)) {
            $data = array_merge($data, $this->data);
        }

        return $data;
    }

    /**
     * Sets the identifier of the target device, eg UUID or similar
     *
     * @param $identifier
     */
    public function setDeviceIdentifier($identifier)
    {
        $this->identifier = $identifier;
        $this->allIdentifiers = array($identifier);
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return ($this->isGCM ? Types::OS_ANDROID_GCM : Types::OS_ANDROID_C2DM);
    }

    /**
     * Returns the target device identifier
     *
     * @return string
     */
    public function getDeviceIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Android-specific
     * Returns the collapse key
     *
     * @return int
     */
    public function getCollapseKey()
    {
        return $this->collapseKey;
    }

    /**
     * Android-specific
     * Sets the collapse key
     *
     * @param $collapseKey
     */
    public function setCollapseKey($collapseKey)
    {
        $this->collapseKey = $collapseKey;
    }

    /**
     * Set whether this is a GCM message
     * (default false)
     *
     * @param $gcm
     */
    public function setGCM($gcm)
    {
        $this->isGCM = !!$gcm;
    }

    /**
     * Returns whether this is a GCM message
     *
     * @return mixed
     */
    public function isGCM()
    {
        return $this->isGCM;
    }

    /**
     * Returns an array of device identifiers
     * Not used in C2DM
     *
     * @return mixed
     */
    public function getGCMIdentifiers()
    {
        return $this->allIdentifiers;
    }

    /**
     * Adds a device identifier to the GCM list
     */
    public function addGCMIdentifier($identifier)
    {
        if (!in_array($identifier, $this->allIdentifiers)) {
            $this->allIdentifiers[] = $identifier;
        }
    }

    /**
     * Sets GCM options
     * @param array $options
     */
    public function setGCMOptions($options)
    {
        $this->gcmOptions = $options;
    }

    /**
     * Returns GCM options
     *
     * @return array
     */
    public function getGCMOptions()
    {
        return $this->gcmOptions;
    }
}
