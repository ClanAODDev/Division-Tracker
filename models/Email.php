<?php

use Mailgun\Mailgun;

class Email
{
    public static $bcc = "admin@aodwebhost.site.nfoservers.com";
    public static $from = "AOD Division Tracker <admin@aodwebhost.site.nfoservers.com>";
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
        $email->message .= "<p><small>If you believe you have received this email in error, or the account was not created by you, please let us know by sending an email to admin@aodwebhost.site.nfoservers.com</small></p>";
        $email->message .= "<p><small>PLEASE DO NOT REPLY TO THIS E-MAIL</small></p>";
        $email->send();
    }


    public function send()
    {
        $mgClient = new Mailgun(MAILGUN_TOKEN);
        $domain = MAILGUN_DOMAIN;

        $result = $mgClient->sendMessage($domain, array(
            'from' => self::$from,
            'to' => $this->to,
            'subject' => $this->subject,
            'text' => $this->message
        ));
    }
}


