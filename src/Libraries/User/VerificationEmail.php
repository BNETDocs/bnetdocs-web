<?php

namespace BNETDocs\Libraries\User;

use \BNETDocs\Interfaces\DatabaseObject;
use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\Template;
use \BNETDocs\Libraries\Db\MariaDb;
use \BNETDocs\Libraries\EventLog\EventTypes;
use \BNETDocs\Libraries\EventLog\Logger;
use \PHPMailer\PHPMailer\PHPMailer;
use \UnexpectedValueException;

/**
 * Sends the email containing a user's account verification link.
 */
class VerificationEmail
{
  /**
   * Seconds a user must wait between verification emails they send themselves.
   */
  public const COOLDOWN = 120;

  /**
   * Why the email is being sent, which changes its wording.
   */
  public const REASON_EMAIL_CHANGED = 'EMAIL_CHANGED'; // the user changed their email address
  public const REASON_REGISTERED = 'REGISTERED'; // the user just registered
  public const REASON_RESENT = 'RESENT'; // the user asked for a new link
  public const REASON_STAFF_RESENT = 'STAFF_RESENT'; // a staff member sent a new link

  private function __construct() {}

  /**
   * Whether an email was sent on behalf of a user within the last COOLDOWN seconds.
   *
   * @param User $actor The user the EMAIL_SENT events are attributed to.
   * @return bool Whether they must wait before sending another.
   */
  public static function sentRecently(User $actor): bool
  {
    $cutoff = (new \DateTimeImmutable('now', new \DateTimeZone(DatabaseObject::DATE_TZ)))
      ->modify(\sprintf('-%d seconds', self::COOLDOWN))->format(DatabaseObject::DATE_SQL);
    $q = MariaDb::instance()->prepare('
      SELECT 1 FROM `event_log`
      WHERE `event_type_id` = ? AND `user_id` = ? AND `event_datetime` >= ? LIMIT 1;
    ');
    try
    {
      return $q && $q->execute([EventTypes::EMAIL_SENT, $actor->getId(), $cutoff]) && $q->rowCount() > 0;
    }
    finally { if ($q) $q->closeCursor(); }
  }

  /**
   * Sends the verification email to the user's current email address, and logs it as EMAIL_SENT.
   *
   * @param User $user The user to verify, which must have a pending verifier token.
   * @param User|int|null $actor Who the EMAIL_SENT event is attributed to.
   * @param string $reason One of the REASON_* constants, which changes its wording.
   * @return void
   * @throws UnexpectedValueException if the user has no pending verifier token.
   * @throws \PHPMailer\PHPMailer\Exception if the email could not be sent.
   */
  public static function send(User $user, User|int|null $actor, string $reason = self::REASON_REGISTERED): void
  {
    $token = $user->getVerifierToken();
    if (empty($token)) throw new UnexpectedValueException('user has no pending verifier token');

    $mail = new PHPMailer(true); // true enables exceptions

    $state = new \StdClass();
    $state->mail = &$mail;
    $state->name = $user->getName();
    $state->reason = $reason;
    $state->token = $token;
    $state->user_id = $user->getId();

    //Server settings
    $mail->Timeout = 10; // default is 300 per RFC2821 $ 4.5.3.2
    $mail->SMTPDebug = 0;
    $mail->isSMTP();
    $mail->Host       = Config::get('email.smtp_host') ?? 'localhost';
    $mail->Username   = Config::get('email.smtp_user') ?? '';
    $mail->Password   = Config::get('email.smtp_password') ?? '';
    $mail->SMTPAuth   = !empty($mail->Username);
    $mail->SMTPSecure = (Config::get('email.smtp_tls') ?? false) ? 'tls' : '';
    $mail->Port       = Config::get('email.smtp_port') ?? 25;

    //Recipients
    $recipient_from = Config::get('email.recipient_from') ?? [];
    if (isset($recipient_from[0]))
    {
      $mail->setFrom($recipient_from[0], $recipient_from[1] ?? '');
    }

    $mail->addAddress($user->getEmail(), $user->getName());

    $recipient_reply_to = Config::get('email.recipient_reply_to') ?? [];
    if (isset($recipient_reply_to[0]))
    {
      $mail->addReplyTo($recipient_reply_to[0], $recipient_reply_to[1] ?? '');
    }

    // Content
    $mail->isHTML(true);
    $mail->Subject = 'Account Verification';
    $mail->CharSet = PHPMailer::CHARSET_UTF8;

    \ob_start();
    (new Template($state, 'Email/User/Register.rich'))->invoke();
    $mail->Body = \ob_get_clean();

    \ob_start();
    (new Template($state, 'Email/User/Register.plain'))->invoke();
    $mail->AltBody = \ob_get_clean();

    $mail->send();

    $event = Logger::initEvent(
      EventTypes::EMAIL_SENT,
      $actor,
      \getenv('REMOTE_ADDR'),
      [
        'from' => $mail->From,
        'to' => $mail->getToAddresses(),
        'reply_to' => $mail->getReplyToAddresses(),
        'subject' => $mail->Subject,
        'content_type' => $mail->ContentType,
        'body' => $mail->Body,
        'alt_body' => $mail->AltBody,
      ]
    );

    if ($event->commit())
    {
      $embed = Logger::initDiscordEmbed($event, $user->getURI());
      Logger::logToDiscord($event, $embed);
    }
  }
}
