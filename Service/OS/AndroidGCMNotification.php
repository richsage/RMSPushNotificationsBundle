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

class AndroidGCMNotification implements OSNotificationServiceInterface
{

    /**
     * Whether or not to use the dry run GCM
     *
     * @var bool
     */
    protected $useDryRun = false;

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
     * @param bool         $dryRun
     */
    public function __construct($apiKey, $useMultiCurl, $timeout, $logger, AbstractCurl $client = null, $dryRun = false)
    {
        $this->useDryRun = $dryRun;
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
     * Sends the data to the given registration IDs via the GCM server
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface              $message
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

        if ($this->useDryRun) {
            $data['dry_run'] = true;
        }

        // Perform the calls (in parallel)
        $this->responses = array();
        $gcmIdentifiers = $message->getGCMIdentifiers();

        if (count($message->getGCMIdentifiers()) == 1) {
            $data['to'] = $gcmIdentifiers[0];
            $this->responses[] = $this->browser->post($this->apiURL, $headers, json_encode($data));
        } else {
            // Chunk number of registration IDs according to the maximum allowed by GCM
            $chunks = array_chunk($message->getGCMIdentifiers(), $this->registrationIdMaxCount);

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

    /**
     * Change Google GCM API key
     *
     * @param string $apiKey
     */
    public function setApiKey($apiKey)
    {
        $this->apiKey = $apiKey;
    }
}
