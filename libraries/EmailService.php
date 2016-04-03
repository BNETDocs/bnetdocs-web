<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\EmailMessage;
use \BNETDocs\Libraries\Emails\User\Register as UserRegisterEmail;

class EmailService {

  public static function prepareEmail(
    EmailMessage &$email, $from, $to, $cc, $bcc, $subject
  ) {
    if (!empty($from)) {
      $email->setHeader("From", $from);
    }
    if (is_string($to) && !empty($to)) {
      $email->setHeader("To", $to);
    } else if (is_null($to)) {
    } else {
      $str = "";
      foreach ($to as $address) {
        if (!empty($str)) $str .= ",";
        $str .= $address;
      }
      $email->setHeader("To", $str);
    }
    if (is_string($cc) && !empty($cc)) {
      $email->setHeader("Cc", $cc);
    } else if (is_null($cc)) {
    } else {
      $str = "";
      foreach ($cc as $address) {
        if (!empty($str)) $str .= ",";
        $str .= $address;
      }
      $email->setHeader("Cc", $str);
    }
    if (is_string($bcc) && !empty($bcc)) {
      $email->setHeader("Bcc", $bcc);
    } else if (is_null($bcc)) {
    } else {
      $str = "";
      foreach ($bcc as $address) {
        if (!empty($str)) $str .= ",";
        $str .= $address;
      }
      $email->setHeader("Bcc", $str);
    }
    if (!empty($subject)) {
      $email->setHeader("Subject", $subject);
    }
    $email->build();
    return true;
  }

  public static function sendEmail(
    EmailMessage &$email, $user_id = null, $ip_address = null
  ) {
    $to        = $email->getHeader("To");
    $subject   = $email->getHeader("Subject");
    $message   = $email->getBody();
    $headers   = $email->getHeaders();
    if (!is_null($to)) {
      $to->rewind();
      if ($to->valid()) {
        $to = $to->current()->getValue();
      }
    }
    if (!is_null($subject)) {
      $subject->rewind();
      if ($subject->valid()) {
        $subject = $subject->current()->getValue();
      }
    }
    $success = mail($to, $subject, $message, $headers);
    $meta_data = json_encode([
      "class"      => get_class($email),
      "successful" => $success
    ]);
    Logger::logEvent("email_sent", $user_id, $ip_address, $meta_data);
    return $success;
  }

}
