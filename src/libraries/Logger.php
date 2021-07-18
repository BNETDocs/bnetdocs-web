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

    try
    {
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

      if ($successful)
      {
        self::dispatchDiscordWebhook((int) Common::$database->lastInsertId());
      }
    }
    catch (PDOException $e)
    {
      throw new QueryException('Cannot log event', $e);
    }
    finally
    {
      return $successful;
    }
  }

  protected static function dispatchDiscordWebhook($event_id)
  {
    $c = Common::$config->discord->forward_event_log;
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
    if (!is_null($user))
    {
      $author = new DiscordEmbedAuthor(
        $user->getName(), $user->getURI(), $user->getAvatarURI(null)
      );
      $embed->setAuthor($author);
    }

    if (!$c->exclude_meta_data)
    {
      $data = json_decode($event->getMetadata(), true);

      $parse_fx = function($value, $key, $embed)
      {
        $field = null;

        if (!$field && is_string($value))
        {
          $v = substr($value, 0, DiscordEmbedField::MAX_VALUE - 3);
          if (strlen($value) > DiscordEmbedField::MAX_VALUE - 3)
          {
            $v .= '...';
          }
          $field = new DiscordEmbedField(
            $key, $v, (strlen($v) < DiscordEmbedField::MAX_VALUE / 4)
          );
        }

        if (!$field && is_numeric($value))
        {
          $field = new DiscordEmbedField($key, $value, true);
        }

        if (!$field && is_bool($value))
        {
          $field = new DiscordEmbedField(
            $key, ($value ? 'true' : 'false'), true
          );
        }

        if (!$field)
        {
          $field = new DiscordEmbedField($key, gettype($value), true);
        }

        $embed->addField($field);
      };

      $flatten_fx = function(&$tree, &$flatten_fx, &$parse_fx, &$depth, &$embed)
      {
        if (!is_array($tree))
        {
          $parse_fx($tree, implode('_', $depth), $embed);
          return;
        }

        array_push($depth, '');
        if (count($depth) > 2) return;

        foreach ($tree as $key => $value)
        {
          $depth[count($depth)-1] = $key;
          $flatten_fx($value, $flatten_fx, $parse_fx, $depth, $embed);
        }

        array_pop($depth);
      };

      $depth = [];
      $flatten_fx($data, $flatten_fx, $parse_fx, $depth, $embed);
    }

    $webhook->addEmbed($embed);
    $webhook->send();
  }
}
