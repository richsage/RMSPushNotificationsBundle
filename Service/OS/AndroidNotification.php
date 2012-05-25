<?php

namespace RMS\PushNotificationsBundle\Service\OS;

use Buzz\Browser;

class AndroidNotification implements OSNotificationServiceInterface
{
    const DEFAULT_COLLAPSE_KEY = 1;

    /**
     * Username for auth
     *
     * @var string
     */
    protected $username;

    /**
     * Password for auth
     *
     * @var string
     */
    protected $password;

    /**
     * The source of the notification
     * eg com.example.myapp
     *
     * @var string
     */
    protected $source;

    /**
     * Authentication token
     *
     * @var string
     */
    protected $authToken;

    /**
     * Constructor
     *
     * @param $username
     * @param $password
     * @param $source
     */
    public function __construct($username, $password, $source)
    {
        $this->username = $username;
        $this->password = $password;
        $this->source = $source;
        $this->authToken = "";
    }

    /**
     * Sends a C2DM message
     * This assumes that a valid auth token can be obtained
     *
     * @param $deviceToken
     * @param $message
     * @param $collapseKey
     * @return bool
     */
    public function send($deviceToken, $message, $collapseKey = self::DEFAULT_COLLAPSE_KEY)
    {
        if ($this->getAuthToken()) {
            $headers[] = "Authorization: GoogleLogin auth=" . $this->authToken;
            $data = array(
                "registration_id" => $deviceToken,
                "collapse_key"    => $collapseKey,
                "data.message"    => $message,
            );

            $buzz = new Browser();
            $buzz->getClient()->setVerifyPeer(false);
            $response = $buzz->post("https://android.apis.google.com/c2dm/send", $headers, http_build_query($data));
            return preg_match("/^id=/", $response->getContent()) > 0;
        }

        return false;
    }


    /**
     * Gets a valid authentication token
     *
     * @return bool
     */
    protected function getAuthToken()
    {
        $data = array(
            "Email"         => $this->username,
            "Passwd"        => $this->password,
            "accountType"   => "HOSTED_OR_GOOGLE",
            "source"        => $this->source,
            "service"       => "ac2dm"
        );

        $buzz = new Browser();
        $buzz->getClient()->setVerifyPeer(false);
        $response = $buzz->post("https://www.google.com/accounts/ClientLogin", array(), http_build_query($data));
        if ($response->getStatusCode() !== 200) {
            return false;
        }

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response->getContent(), $matches);
        $this->authToken = $matches[1];
        return true;
    }
}
