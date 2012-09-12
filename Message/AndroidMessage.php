<?php

namespace RMS\PushNotificationsBundle\Message;

use RMS\PushNotificationsBundle\Device\Types;

class AndroidMessage
{

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
     * Registration IDS
     *
     * @var array
     */
    protected $registrationIds = array();
    
    /**
     * Options to add along with message, such as collapse_key, time_to_live, delay_while_idle
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
     * Get the data
     * 
     * @return array
     */
    public function getData() 
    {
        return $this->data;
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
     * Returns the target devices registration ids
     *
     * @return array
     */
    public function getDevicesRegistrationIds()
    {
        return $this->registrationIds;
    }
    
    /**
     * Add target device registration id to array
     * 
     * @param string $registrationId
     */
    public function addDeviceRegistrationId($registrationId) 
    {
        $this->registrationIds[] = $registrationId;
    }

    /**
     * Sets the target devices registration ids
     *
     * @param array $registrationIds
     */
    public function setDevicesRegistrationIds(array $registrationIds)
    {
        $this->registrationIds = $registrationIds;
    }
    
    /**
     * Get optional options to add along with message, such as collapse_key, time_to_live, delay_while_idle
     * 
     * @return array
     */
    public function getOptions() 
    {
        return $this->options;
    }
    
    /**
     * Set optional options to add along with message, such as collapse_key, time_to_live, delay_while_idle
     * 
     * @param array $options
     */
    public function setOptions(array $options) 
    {
        $this->options = $options;
    }

    /**
     * Returns the target OS for this message
     *
     * @return string
     */
    public function getTargetOS()
    {
        return Types::OS_ANDROID;
    }

}
