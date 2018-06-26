<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Psr\Log\LoggerInterface,
    Psr\Http\Message\ResponseInterface;
use RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException,
    RMS\PushNotificationsBundle\Message\BlackberryMessage,
    RMS\PushNotificationsBundle\Message\MessageInterface;
use Buzz\Browser,
    Buzz\Client\Curl,
    Buzz\Middleware\BasicAuthMiddleware;

class BlackberryNotification implements OSNotificationServiceInterface
{
    /**
     * Evaluation mode or not
     *
     * @var string
     */
    protected $evaluation;

    /**
     * App ID
     *
     * @var string
     */
    protected $appID;

    /**
     * Password for auth
     *
     * @var string
     */
    protected $password;

    /**
     * Timeout in seconds for the connecting client
     *
     * @var int
     */
    protected $timeout;

    /**
     * Monolog logger
     *
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Constructor
     *
     * @param $evaluation
     * @param $appID
     * @param $password
     * @param $timeout
     * @param $logger
     */
    public function __construct($evaluation, $appID, $password, $timeout, $logger)
    {
        $this->evaluation = $evaluation;
        $this->appID = $appID;
        $this->password = $password;
        $this->timeout = $timeout;
        $this->logger = $logger;
    }

    /**
     * Sends a Blackberry Push message
     *
     * @param  \RMS\PushNotificationsBundle\Message\MessageInterface              $message
     * @throws \RMS\PushNotificationsBundle\Exception\InvalidMessageTypeException
     * @return bool
     */
    public function send(MessageInterface $message)
    {
        if (!$message instanceof BlackberryMessage) {
            throw new InvalidMessageTypeException(sprintf("Message type '%s' not supported by Blackberry", get_class($message)));
        }

        return $this->doSend($message);
    }

    /**
     * Does the actual sending
     *
     * @param  \RMS\PushNotificationsBundle\Message\BlackberryMessage $message
     * @return bool
     */
    protected function doSend(BlackberryMessage $message)
    {
        $separator = "mPsbVQo0a68eIL3OAxnm";
        $body = $this->constructMessageBody($message, $separator);
        $browser = new Browser(new Curl(array('timeout' => $this->timeout)));
        $browser->addMiddleware(new BasicAuthMiddleware($this->appID, $this->password));

        $url = "https://pushapi.na.blackberry.com/mss/PD_pushRequest";
        if ($this->evaluation) {
            $url = "https://pushapi.eval.blackberry.com/mss/PD_pushRequest";
        }
        $headers = array();
        $headers[] = "Content-Type: multipart/related; boundary={$separator}; type=application/xml";
        $headers[] = "Accept: text/html, *";
        $headers[] = "Connection: Keep-Alive";

        $response = $browser->post($url, $headers, $body);

        return $this->parseResponse($response);
    }

    /**
     * Builds the actual body of the message
     *
     * @param \RMS\PushNotificationsBundle\Message\BlackberryMessage $message
     * @param $separator
     * @return string
     */
    protected function constructMessageBody(BlackberryMessage $message, $separator)
    {
        $data = "";
        $messageID = microtime(true);

        $data .= "--" . $separator . "\r\n";
        $data .= "Content-Type: application/xml; charset=UTF-8\r\n\r\n";
        $data .= $this->getXMLBody($message, $messageID) . "\r\n";
        $data .= "--" . $separator . "\r\n";
        $data .= "Content-Type: text/plain\r\n";
        $data .= "Push-Message-ID: {$messageID}\r\n\r\n";
        if (is_array($message->getMessageBody())) {
            $data .= json_encode($message->getMessageBody());
        } else {
            $data .= $message->getMessageBody();
        }
        $data .= "\r\n";
        $data .= "--" . $separator . "--\r\n";

        return $data;
    }

    /**
     * Handles and parses the response
     * Returns a value indicating success/fail
     *
     * @param ResponseInterface $response
     * @return bool
     */
    protected function parseResponse(ResponseInterface $response)
    {
        if (null !== $response->getStatusCode() && $response->getStatusCode() !== 200) {
            return false;
        }
        $response->getBody()->rewind();
        $doc = new \DOMDocument();
        $doc->loadXML($response->getBody()->getContents());
        $elems = $doc->getElementsByTagName("response-result");
        if (!$elems->length) {
            $this->logger->error('Response is empty');
            return false;
        }
        $responseElement = $elems->item(0);
        if ($responseElement->getAttribute("code") != "1001") {
            $this->logger->error($responseElement->getAttribute("code"). ' : '. $responseElement->getAttribute("desc"));
        }

        return ($responseElement->getAttribute("code") == "1001");
    }

    /**
     * Create the XML body that accompanies the actual push data
     *
     * @param $messageID
     * @return string
     */
    private function getXMLBody(BlackberryMessage $message, $messageID)
    {
        $deliverBefore = gmdate('Y-m-d\TH:i:s\Z', strtotime('+5 minutes'));
        $impl = new \DOMImplementation();
        $dtd = $impl->createDocumentType(
            "pap",
            "-//WAPFORUM//DTD PAP 2.1//EN",
            "http://www.openmobilealliance.org/tech/DTD/pap_2.1.dtd"
        );
        $doc = $impl->createDocument("", "", $dtd);

        // Build it centre-out
        $pm = $doc->createElement("push-message");
        $pm->setAttribute("push-id", $messageID);
        $pm->setAttribute("deliver-before-timestamp", $deliverBefore);
        $pm->setAttribute("source-reference", $this->appID);

        $qos = $doc->createElement("quality-of-service");
        $qos->setAttribute("delivery-method", "unconfirmed");
        $add = $doc->createElement("address");
        $add->setAttribute("address-value", $message->getDeviceIdentifier());

        $pm->appendChild($add);
        $pm->appendChild($qos);
        $pap = $doc->createElement("pap");
        $pap->appendChild($pm);
        $doc->appendChild($pap);

        return $doc->saveXML();
    }
}
