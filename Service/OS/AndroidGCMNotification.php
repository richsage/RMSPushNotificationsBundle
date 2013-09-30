<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface;
use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\AndroidMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser,
    Buzz\Client\MultiCurl;

class AndroidGCMNotification implements OSNotificationServiceInterface
{
    /**
     * GCM endpoint
     *
     * @var string
     */
    protected $apiURL = "https://android.googleapis.com/gcm/send";

    /**
     * Google GCM API key
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
     * Collection of the responses from the GCM communication
     *
     * @var array
     */
    protected $responses;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param $apiKey
     * @param MultiCurl $client (optional)
     */
    public function __construct($apiKey, MultiCurl $client = null)
    {
        $this->apiKey = $apiKey;
        $this->browser = new Browser($client ?: new MultiCurl());
    }

    /**
     * Set the logger to use
     *
     * @param LoggerInterface $logger
     * @return mixed|void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Sends the data to the given registration IDs via the GCM server
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
        if (!$message->isGCM()) {
            throw new InvalidMessageTypeException("Non-GCM messages not supported by the Android GCM sender");
        }

        $headers = array(
            "Authorization: key=" . $this->apiKey,
            "Content-Type: application/json",
        );
        $data = array_merge(
            $message->getGCMOptions(),
            array("data" => $message->getData())
        );

        // Chunk number of registration IDs according to the maximum allowed by GCM
        $chunks = array_chunk($message->getGCMIdentifiers(), $this->registrationIdMaxCount);

        // Perform the calls (in parallel)
        $this->responses = array();
        $failures = false;
        foreach ($chunks as $chunkID => $registrationIDs) {
            $data["registration_ids"] = $registrationIDs;
            $response = $this->browser->post($this->apiURL, $headers, json_encode($data));
            $this->responses[] = $response;

            $statusCode = $response->getStatusCode();
            if ($statusCode != 200) {
                $this->logger->error("GCM status code: {statusCode}", array("statusCode" => $statusCode));
            }
            $message = json_decode($response->getContent());
            if ($message === null || $message->success == 0 || $message->failure > 0) {
                $this->logger->error("GCM error received: {error}, for chunk ID {chunkID}", array("error" => $message->results[0]->error, "chunkID" => $chunkID, "registrationIDs" => $registrationIDs));
                $failures = true;
            }
        }
        $this->browser->getClient()->flush();

        return !$failures;
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
