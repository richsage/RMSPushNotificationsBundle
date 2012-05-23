<?php

namespace RMS\PushNotificationsBundle\Service\OS;

class AndroidNotification implements OSNotificationServiceInterface
{
    protected $username;
    protected $password;
    protected $authToken;

    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
        $this->authToken = "";
    }

    public function send($deviceToken, $message, $messageType = null)
    {
        $this->getAuthToken();
        if ($this->authToken) {
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
            curl_exec($curl);
            curl_close($curl);
        }
    }

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
            "source"        => "Company-AppName-Version",
            "service"       => "ac2dm"
        );

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);

        $response = curl_exec($curl);
        curl_close($curl);

        if (strpos($response, "200 OK") === false) {
            return false;
        }

        preg_match("/Auth=([a-z0-9_\-]+)/i", $response, $matches);
        $this->authToken = $matches[1];
    }
}
