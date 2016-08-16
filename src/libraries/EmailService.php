<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\EmailMessage;
use \BNETDocs\Libraries\Email\Debug as DebugEmail;
use \BNETDocs\Libraries\Email\User\Register as UserRegisterEmail;
use \InvalidArgumentException;

class EmailService {

  protected static function getHeaderValue($value) {
    if (is_null($value)) {
      return null;
    }
    if (is_string($value) && !empty($value)) {
      return $value;
    }
    if (!is_array($value)) {
      throw new InvalidArgumentException(
        gettype($value) . " given; array, null, or string expected"
      );
    }
    $buffer = "";
    foreach ($value as $address) {
      if (empty($address)) continue;
      if (!empty($buffer)) $buffer .= ",";
      $buffer .= $address;
    }
    return $buffer;
  }

  public static function prepareEmail(
    EmailMessage &$email, $from, $to, $cc, $bcc, $subject
  ) {
    $clean_from = self::getHeaderValue($from);
    $clean_to   = self::getHeaderValue($to);
    $clean_cc   = self::getHeaderValue($cc);
    $clean_bcc  = self::getHeaderValue($bcc);

    if ($clean_from) $email->setHeader("From"   , $from   );
    if ($clean_to  ) $email->setHeader("To"     , $to     );
    if ($clean_cc  ) $email->setHeader("Cc"     , $cc     );
    if ($clean_bcc ) $email->setHeader("Bcc"    , $bcc    );
    if ($subject   ) $email->setHeader("Subject", $subject);

    return $email->build();
  }

  public static function sendEmail(
    EmailMessage &$email, $user_id = null, $ip_address = null
  ) {
    $to      = $email->getHeaderFirst("To");
    $subject = $email->getHeaderFirst("Subject");
    $message = $email->getBody();
    $headers = $email->getHeaders();

    if (is_null($to     )) $to      = "";
    if (is_null($subject)) $subject = "";

    $success = mail($to, $subject, $message, $headers);

    $meta_data = json_encode([
      "class"      => get_class($email),
      "successful" => $success
    ]);

    Logger::logEvent("email_sent", $user_id, $ip_address, $meta_data);

    return $success;
  }

}
