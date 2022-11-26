<?php
namespace BNETDocs\Libraries;

use \BNETDocs\Libraries\Database;
use \BNETDocs\Libraries\Discord\Embed as DiscordEmbed;
use \BNETDocs\Libraries\Discord\EmbedAuthor as DiscordEmbedAuthor;
use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\Discord\Webhook as DiscordWebhook;
use \BNETDocs\Libraries\Event;
use \CarlBennett\MVC\Libraries\Common;

class Logger extends \CarlBennett\MVC\Libraries\Logger
{
  public static function logEvent(
    $event_type_id, $user_id = null, $ip_address = null, $meta_data = null
  ) : bool
  {
    $q = Database::instance()->prepare('
      INSERT INTO `event_log` (
        `event_type_id`, `event_datetime`, `user_id`, `ip_address`, `meta_data`
      ) VALUES (
        :etid, NOW(), :uid, :ip, :data
      );
    ');
    $p = [
      ':etid' => $event_type_id,
      ':uid' => $user_id,
      ':ip' => $ip_address,
      ':data' => $meta_data,
    ];
    if (!$q || !$q->execute($p)) return false;
    self::dispatchDiscordWebhook((int) Database::instance()->lastInsertId());
    return true;
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
      $data = $event->getMetadata();
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
          if (strlen($value) == 0)
          {
            $v = '*(empty)*';
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
