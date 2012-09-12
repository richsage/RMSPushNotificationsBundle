<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\AndroidMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser,
    Buzz\Client\MultiCurl;

class AndroidNotification implements OSNotificationServiceInterface
{
    
    /**
     * @var string
     */
    protected $apiUrl = 'https://android.googleapis.com/gcm/send';
    
    /**
     * Google GCM API key
     *
     * @var string
     */
    protected $apiKey;
    
    /**
     * @var string
     */
    protected $registrationIdMaxCount = 1000;

    /**
     * @var \Buzz\Browser
     */
    protected $browser;

    /**
     * @var array
     */
    protected $responses;

    /**
     * Class constructor
     *
     * @param string $apiKey
     */
    public function __construct($apiKey)
    {
        $this->apiKey = $apiKey;
        $this->browser = new Browser(new MultiCurl());
    }

    /**
     * Sends the data to the given registration ID's via the GCM server
     *
     * @param \RMS\PushNotificationsBundle\Message\MessageInterface $message
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof AndroidMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by GCM", get_class($message)));
        }
        
        $headers = array(
            'Authorization: key='.$this->apiKey,
            'Content-Type: application/json'
        );

        $data = array_merge($message->getOptions(), array(
            'data' => $message->getData(),
        ));

        // Chunk number of registration ID's according to the maximum allowed by GCM
        $chunks = array_chunk($message->getDevicesRegistrationIds(), $this->registrationIdMaxCount);

        // Perform the calls (in parallel)
        $this->responses = array();
        foreach ($chunks as $registrationIds) {
            $data['registration_ids'] = $registrationIds;
            $this->responses[] = $this->browser->post($this->apiUrl, $headers, json_encode($data));
        }
        $this->browser->getClient()->flush();

        // Determine success
        foreach ($this->responses as $response) {
            $message = json_decode($response->getContent());
            if ($message === null || $message->success == 0 || $message->failure > 0) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }
    
}
