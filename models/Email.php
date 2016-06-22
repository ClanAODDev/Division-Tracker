<?php

use Http\Adapter\Guzzle6\Client;
use Mailgun\Mailgun;

class Email
{
    public static $from = "AOD Division Tracker <admin@aod-tracker.com>";
    public $to;
    public $subject;
    public $message;
    public $headers;

    public static function validate(User $user)
    {
        $email = new self();
        $email->to = $user->email;
        $email->subject = "AOD Division Tracker - Email verification";
        $email->message .= "<h1><strong>{$user->username}</strong>,</h1>";
        $email->message .= "<p>This email was used by someone with the IP {$_SERVER['REMOTE_ADDR']} to create an account on the AOD Division Tracker. Please verify that it was you by clicking the link provided below, or copy-paste the URL into your browser's address bar.</p>";
        $email->message .= "<p>http://aod-tracker.com/tracker/authenticate?id={$user->validation}\r\n\r\n</p>";
        $email->message .= "<p><small>PLEASE DO NOT REPLY TO THIS E-MAIL</small></p>";
        $email->send();
    }


    public function send()
    {

        $client = new Client();
        $mailgun = new Mailgun(MAILGUN_TOKEN, $client);
        $domain = MAILGUN_DOMAIN;

        $result = $mailgun->sendMessage($domain, [
            'from' => self::$from,
            'to' => $this->to,
            'html' => $this->message,
            'subject' => $this->subject,
            'text' => "This email was used by someone with the IP {$_SERVER['REMOTE_ADDR']} to create an account on the AOD Division Tracker. Please verify that it was you by clicking the link provided below, or copy-paste the URL into your browser\'s address bar. \r\n\r\nhttp://aod-tracker.com/tracker/authenticate?id={$user->validation}\r\n\r\nPLEASE DO NOT REPLY TO THIS E-MAIL";
        ]);
    }
}


