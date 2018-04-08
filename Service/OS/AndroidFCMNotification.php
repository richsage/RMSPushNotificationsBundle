<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface;
use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\AndroidMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser,
    Buzz\Client\AbstractCurl,
    Buzz\Client\Curl,
    Buzz\Client\MultiCurl;

class AndroidFCMNotification implements OSNotificationServiceInterface
{

    /**
     * FCM endpoint
     *
     * @var string
     */
    protected $apiURL = "https://fcm.googleapis.com/fcm/send";

    /**
     * Google FCM API key
     *
     * @var string
     */
    protected $apiKey;

    /**
     * Max registration count
     *
     * @var integer
     */
    protected $registrationIdMaxCount = 1000;

    /**
     * Browser object
     *
     * @var \Buzz\Browser
     */
    protected $browser;

    /**
     * Collection of the responses from the FCM communication
     *
     * @var array
     */
    protected $responses;

    /**
     * Monolog logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param string       $apiKey
     * @param bool         $useMultiCurl
     * @param int          $timeout
     * @param LoggerInterface $logger
     * @param AbstractCurl $client (optional)
     */
    public function __construct($apiKey, $useMultiCurl, $timeout, $logger, AbstractCurl $client = null)
    {
        $this->apiKey = $apiKey;
        if (!$client) {
            $client = ($useMultiCurl ? new MultiCurl() : new Curl());
        }
        $client->setTimeout($timeout);

        $this->browser = new Browser($client);
        $this->browser->getClient()->setVerifyPeer(false);
        $this->logger = $logger;
    }

    /**
     * Sends the data to the given registration IDs via the FCM server
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface              $message
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof AndroidMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by FCM", get_class($message)));
        }
        if (!$message->isFCM()) {
            throw new InvalidMessageTypeException("Non-FCM messages not supported by the Android FCM sender");
        }

        $headers = array(
            "Authorization: key=" . $this->apiKey,
            "Content-Type: application/json",
        );
        $data = array_merge(
            $message->getFCMOptions(),
            array("notification" => $message->getData())
        );
        
        // Perform the calls (in parallel)
        $this->responses = array();
        $fcmIdentifiers = $message->getFCMIdentifiers();

        if (count($message->getFCMIdentifiers()) == 1) {
            $data['to'] = $fcmIdentifiers[0];
            $this->responses[] = $this->browser->post($this->apiURL, $headers, json_encode($data));
        } else {
            // Chunk number of registration IDs according to the maximum allowed by FCM
            $chunks = array_chunk($message->getFCMIdentifiers(), $this->registrationIdMaxCount);

            foreach ($chunks as $registrationIDs) {
                $data['registration_ids'] = $registrationIDs;
                $this->responses[] = $this->browser->post($this->apiURL, $headers, json_encode($data));
            }
        }

        // If we're using multiple concurrent connections via MultiCurl
        // then we should flush all requests
        if ($this->browser->getClient() instanceof MultiCurl) {
            $this->browser->getClient()->flush();
        }

        // Determine success
        foreach ($this->responses as $response) {
            $message = json_decode($response->getContent());
            if ($message === null || $message->success == 0 || $message->failure > 0) {
                if ($message == null) {
                    $this->logger->error($response->getContent());
                } else {
                    foreach ($message->results as $result) {
                        if (isset($result->error)) {
                            $this->logger->error($result->error);
                        }
                    }
                }
                return false;
            }
        }

        return true;
    }

    /**
     * Returns responses
     *
     * @return array
     */
    public function getResponses()
    {
        return $this->responses;
    }
}
