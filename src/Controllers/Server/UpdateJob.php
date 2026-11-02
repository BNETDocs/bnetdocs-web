<?php

namespace BNETDocs\Controllers\Server;

use \BNETDocs\Libraries\Core\Config;
use \BNETDocs\Libraries\Core\DateTimeImmutable;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\Discord\Embed as DiscordEmbed;
use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\Discord\Webhook as DiscordWebhook;
use \BNETDocs\Libraries\Server\Server;
use \DateTimeZone;

class UpdateJob extends \BNETDocs\Controllers\Base
{
  public const S_DISABLED = ':no_entry: Disabled';
  public const S_ONLINE   = ':white_check_mark: Online';
  public const S_OFFLINE  = ':x: Offline';

  public function __construct()
  {
    $this->model = new \BNETDocs\Models\Server\UpdateJob();
  }

  public function invoke(?array $args): bool
  {
    if (Router::requestMethod() !== Router::METHOD_POST)
    {
      $this->model->_responseCode = HttpCode::HTTP_METHOD_NOT_ALLOWED;
      $this->model->_responseHeaders['Allow'] = Router::METHOD_POST;
      return true;
    }

    $q = Router::query();
    $server_id = isset($q['id']) ? (int) $q['id'] : null;
    $job_token = isset($q['job_token']) ? $q['job_token'] : null;
    $status = isset($q['status']) ? (int) $q['status'] : null;

    if ($job_token !== \BNETDocs\Libraries\Core\Config::get('bnetdocs.server_update_job_token'))
    {
      $this->model->_responseCode = HttpCode::HTTP_FORBIDDEN;
      return true;
    }

    if (!is_int($server_id) || !is_int($status))
    {
      $this->model->_responseCode = HttpCode::HTTP_BAD_REQUEST;
      return true;
    }

    try { $this->model->server = new Server($server_id); }
    catch (\UnexpectedValueException) { $this->model->server = null; }

    if (!$this->model->server)
    {
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      return true;
    }

    $this->model->old_status_bitmask = $this->model->server->getStatusBitmask();
    $this->model->server->setStatusBitmask($status);
    if (!$this->model->server->commit()) return true;

    $discord = Config::get('discord.forward_server_updates');
    if (\is_array($discord) && ($discord['enabled'] ?? false) && !empty($discord['webhook'])
      && !\in_array($server_id, $discord['ignore_server_ids'] ?? []))
    {
      self::dispatchDiscord(
        $this->model->server, $discord['webhook'], $this->model->old_status_bitmask, $status
      );
    }

    $this->model->_responseCode = HttpCode::HTTP_OK;
    return true;
  }

  protected static function dispatchDiscord(Server $server, string $webhook, int $old, int $new): void
  {
    if ($old === $new) return;

    if ($old & Server::STATUS_DISABLED) $old_status = self::S_DISABLED;
    else if ($old & Server::STATUS_ONLINE) $old_status = self::S_ONLINE;
    else if (!($old & Server::STATUS_ONLINE)) $old_status = self::S_OFFLINE;
    else $old_status = sprintf('Unknown (%d)', $old);

    if ($new & Server::STATUS_DISABLED)
    {
      $title = 'Server Disabled';
      $new_status = self::S_DISABLED;
    }
    else if ($new & Server::STATUS_ONLINE)
    {
      $title = 'Server Online';
      $new_status = self::S_ONLINE;
    }
    else if (!($new & Server::STATUS_ONLINE))
    {
      $title = 'Server Offline';
      $new_status = self::S_OFFLINE;
    }
    else
    {
      $title = 'Generic Status Change';
      $new_status = sprintf('Unknown (%d)', $new);
    }

    $label = $server->getLabel();
    if (!empty($label)) $title .= ': ' . $label;

    $webhook = new DiscordWebhook($webhook);

    $embed = new DiscordEmbed();
    $embed->setUrl($server->getURI());
    $embed->setTitle($title);
    $embed->setTimestamp(new DateTimeImmutable('now', new DateTimeZone('Etc/UTC')));

    $data = [];
    $data['Type'] = $server->getType()->getLabel();
    if (!empty($label)) $data['Label'] = $label;
    $data['Server'] = $server->getAddress() . ':' . $server->getPort();
    $data['Status'] = $old_status . ' → ' . $new_status;

    foreach ($data as $key => $value)
    {
      $embed->addField(new DiscordEmbedField($key, $value, true));
    }

    $webhook->addEmbed($embed);
    $webhook->send();
  }
}
