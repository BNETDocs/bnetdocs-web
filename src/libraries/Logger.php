<?php

namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Discord\Embed as DiscordEmbed;
use \BNETDocs\Libraries\Discord\EmbedAuthor as DiscordEmbedAuthor;
use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\Discord\Webhook as DiscordWebhook;
use \BNETDocs\Libraries\Event;
use \BNETDocs\Libraries\Exceptions\QueryException;
use \BNETDocs\Libraries\User;
use \BNETDocs\Libraries\VersionInfo;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Logger as LoggerMVCLib;

use \Exception;
use \InvalidArgumentException;
use \PDO;
use \PDOException;
use \RuntimeException;

class Logger extends LoggerMVCLib {

  public static function logEvent(
    $event_type_id, $user_id = null, $ip_address = null, $meta_data = null
  ) {
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $successful = false;

    try {

      $stmt = Common::$database->prepare('
        INSERT INTO `event_log` (
          `event_type_id`, `event_datetime`, `user_id`, `ip_address`,
          `meta_data`
        ) VALUES (
          :event_type_id, NOW(), :user_id, :ip_address, :meta_data
        );
      ');

      $stmt->bindParam(':event_type_id', $event_type_id, PDO::PARAM_INT);

      $t = (is_null($user_id) ? PDO::PARAM_NULL : PDO::PARAM_INT);
      $stmt->bindParam(':user_id', $user_id, $t);

      $t = (is_null($ip_address) ? PDO::PARAM_NULL : PDO::PARAM_STR);
      $stmt->bindParam(':ip_address', $ip_address, $t);

      $t = (is_null($meta_data) ? PDO::PARAM_NULL : PDO::PARAM_STR);
      $stmt->bindParam(':meta_data', $meta_data, $t);

      $successful = $stmt->execute();
      $stmt->closeCursor();

      if ($successful) {
        self::dispatchDiscordWebhook((int) Common::$database->lastInsertId());
      }

    } catch (PDOException $e) {
      throw new QueryException('Cannot log event', $e);

    } finally {
      return $successful;
    }
  }

  protected static function dispatchDiscordWebhook($event_id) {
    $c = Common::$config->bnetdocs->discord->forward_event_log;
    if (!$c->enabled) return;

    $event = new Event($event_id);
    if (in_array($event->getEventTypeId(), $c->ignore_event_types)) return;

    $webhook = new DiscordWebhook($c->webhook);
    $embed   = new DiscordEmbed();

    $embed->setUrl(Common::relativeUrlToAbsolute(sprintf(
      '/eventlog/view?id=%d', $event_id
    )));

    $embed->setTitle($event->getEventTypeName());
    $embed->setTimestamp($event->getEventDateTime());

    $user = $event->getUser();
    if (!is_null($user)) {
      $author = new DiscordEmbedAuthor(
        $user->getName(), $user->getURI(), $user->getAvatarURI(null)
      );
      $embed->setAuthor($author);
    }

    $data = json_decode($event->getMetadata(), true);
    if (is_scalar($data)) {

      if (is_string($data)) {
        $f_value = substr($data, 0, DiscordEmbedField::MAX_VALUE - 3);
        if (strlen($data) > DiscordEmbedField::MAX_VALUE - 3)
          $f_value .= '...';
      } else {
        $f_value = $data;
      }

      $field = new DiscordEmbedField('Meta Data', $f_value, true);
      $embed->addField($field);

    } else {

      foreach ($data as $key => $value) {

        $f_key = substr($key, 0, DiscordEmbedField::MAX_NAME - 3);
        if (strlen($key) > DiscordEmbedField::MAX_NAME - 3)
          $f_key .= '...';

        if (is_scalar($value)) {

          if (is_string($value)) {
            $f_value = substr($value, 0, DiscordEmbedField::MAX_VALUE - 3);
            if (strlen($value) > DiscordEmbedField::MAX_VALUE - 3)
              $f_value .= '...';
          } else {
            $f_value = $value;
          }

          $field = new DiscordEmbedField($f_key, $f_value, true);

        } else {

          $field = new DiscordEmbedField($f_key, gettype($value), true);

        }
        $embed->addField($field);

        if ($embed->fieldCount() >= DiscordEmbed::MAX_FIELDS) break;
      }

    }

    $webhook->addEmbed($embed);
    $webhook->send();
  }

}
