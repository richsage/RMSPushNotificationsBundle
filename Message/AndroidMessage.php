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
     * A collection of device identifiers that the message
     * is intended for. GCM use only
     *
     * @var array
     */
    protected $allIdentifiers = array();

    /**
     * Options for messages
     *
     * @var array
     */
    protected $options = array();

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
     * Not used here
     *
     * @return array
     */
    public function getMessageBody()
    {
        return array(); // Not used
    }

    /**
     * Sets the identifier of the target device, eg UUID or similar
     *
     * @param $identifier
     */
    public function setDeviceIdentifier($identifier)
    {
        $this->identifier = $identifier;
        $this->allIdentifiers = array($identifier => $identifier);
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return Types::OS_ANDROID_GCM;
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
     * Returns an array of device identifiers
     *
     * @return mixed
     */
    public function getIdentifiers()
    {
        return array_values($this->allIdentifiers);
    }

    /**
     * Adds a device identifier to the list
     * @param string $identifier
     */
    public function addIdentifier($identifier)
    {
        $this->allIdentifiers[$identifier] = $identifier;
    }

    /**
     * Sets the identifier list
     * @param array $allIdentifiers
     */
    public function setAllIdentifiers($allIdentifiers) {
        $this->allIdentifiers = array_combine($allIdentifiers, $allIdentifiers);
    }

    /**
     * Sets message options
     * @param array $options
     */
    public function setOptions($options)
    {
        $this->options = $options;
    }

    /**
     * Returns message options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
