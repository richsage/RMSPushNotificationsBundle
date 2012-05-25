<?php

namespace RMS\PushNotificationsBundle\Service\OS;

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
     * @param $messageType
     * @return bool
     */
    public function send($deviceToken, $message, $messageType = self::DEFAULT_COLLAPSE_KEY)
    {
        if ($this->getAuthToken()) {
            $headers[] = "Authorization: GoogleLogin auth=" . $this->authToken;
            $data = array(
                "registration_id" => $deviceToken,
                "collapse_key"    => $messageType,
                "data.message"    => $message,
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, "https://android.apis.google.com/c2dm/send");
            curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            $response = curl_exec($curl);
            curl_close($curl);

            return preg_match("/^id=/", $response) > 0;
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
        $curl = curl_init();
        if (!$curl) {
            return false;
        }

        curl_setopt($curl, CURLOPT_URL, "https://www.google.com/accounts/ClientLogin");
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);

        $data = array(
            "Email"         => $this->username,
            "Passwd"        => $this->password,
            "accountType"   => "HOSTED_OR_GOOGLE",
            "source"        => $this->source,
            "service"       => "ac2dm"
        );

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode !== 200) {
            return false;
        }

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response, $matches);
        $this->authToken = $matches[1];
        return true;
    }
}
