<?php

class Slack
{
    private $webhook = "https://hooks.slack.com/services/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";
    private $message;

    /**
     * @param $message
     * @return $this
     */
    public function message($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * @return mixed
     */
    public function sendPayload()
    {
        $data = [
            "username" => "Tracker-v2",
            "icon_url" => "http://aodwebhost.site.nfoservers.com/tracker/assets/images/icons/small/tracker.png",
            "text" => $this->message
        ];

        $payload = json_encode($data);
        $this->curlRequest($payload);
    }

    /**
     * @param $payload
     * @return mixed
     */
    private function curlRequest($payload)
    {
        $ch = curl_init($this->webhook);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

        curl_exec($ch);

    }

}