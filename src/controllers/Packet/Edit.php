<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\CSRF;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Packet\Edit as PacketEditModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;

class Edit extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $data              = $router->getRequestQueryArray();
    $model             = new PacketEditModel();
    $model->csrf_id    = mt_rand();
    $model->csrf_token = CSRF::generate($model->csrf_id, 900); // 15 mins
    $model->error      = null;
    $model->format     = null;
    $model->id         = null;
    $model->markdown   = null;
    $model->name       = null;
    $model->packet     = null;
    $model->packet_id  = (isset($data["id"]) ? $data["id"] : null);
    $model->published  = null;
    $model->remarks    = null;
    $model->user = (
      isset($_SESSION['user_id']) ? new User($_SESSION['user_id']) : null
    );

    $model->acl_allowed = ($model->user && $model->user->getAcl(
      User::OPTION_ACL_PACKET_MODIFY
    ));

    try { $model->packet = new Packet($model->packet_id); }
    catch (PacketNotFoundException $e) { $model->packet = null; }
    catch (InvalidArgumentException $e) { $model->packet = null; }

    if ($model->packet === null) {
      $model->error = "NOT_FOUND";
    } else {
      $flags = $model->packet->getOptionsBitmask();

      $model->id        = $model->packet->getPacketId();
      $model->name      = $model->packet->getPacketName();
      $model->format    = $model->packet->getPacketFormat();
      $model->remarks   = $model->packet->getPacketRemarks(false);
      $model->markdown  = ($flags & Packet::OPTION_MARKDOWN);
      $model->published = ($flags & Packet::OPTION_PUBLISHED);

      if ($router->getRequestMethod() == "POST") {
        $this->handlePost($router, $model);
      }
    }

    $view->render($model);

    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected function handlePost(Router &$router, PacketEditModel &$model) {
    if (!$model->acl_allowed) {
      $model->error = "ACL_NOT_SET";
      return;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $data       = $router->getRequestBodyArray();
    $csrf_id    = (isset($data["csrf_id"   ]) ? $data["csrf_id"   ] : null);
    $csrf_token = (isset($data["csrf_token"]) ? $data["csrf_token"] : null);
    $csrf_valid = CSRF::validate($csrf_id, $csrf_token);
    $id         = (isset($data["id"        ]) ? $data["id"        ] : null);
    $name       = (isset($data["name"      ]) ? $data["name"      ] : null);
    $format     = (isset($data["format"    ]) ? $data["format"    ] : null);
    $remarks    = (isset($data["remarks"   ]) ? $data["remarks"   ] : null);
    $markdown   = (isset($data["markdown"  ]) ? $data["markdown"  ] : null);
    $content    = (isset($data["content"   ]) ? $data["content"   ] : null);
    $publish    = (isset($data["publish"   ]) ? $data["publish"   ] : null);
    $save       = (isset($data["save"      ]) ? $data["save"      ] : null);

    $model->id       = $id;
    $model->name     = $name;
    $model->format   = $format;
    $model->remarks  = $remarks;
    $model->markdown = $markdown;
    $model->content  = $content;

    if (!$csrf_valid) {
      $model->error = "INVALID_CSRF";
      return;
    }
    CSRF::invalidate($csrf_id);

    if (empty($name)) {
      $model->error = "EMPTY_NAME";
    } else if (empty($format)) {
      $model->error = "EMPTY_FORMAT";
    } else if (empty($remarks)) {
      $model->error = "EMPTY_REMARKS";
    }

    $user_id = $model->user->getId();

    try {

      $model->packet->setPacketId($model->id);
      $model->packet->setPacketName($model->name);
      $model->packet->setPacketFormat($model->format);
      $model->packet->setPacketRemarks($model->remarks);
      $model->packet->setMarkdown($model->markdown);
      $model->packet->setPublished($publish);

      $model->packet->setEditedCount(
        $model->packet->getEditedCount() + 1
      );
      $model->packet->setEditedDateTime(
        new DateTime("now", new DateTimeZone("UTC"))
      );

      $success = $model->packet->save();

    } catch (QueryException $e) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);

      $success = false;

    }

    if (!$success) {
      $model->error = "INTERNAL_ERROR";
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      "packet_edited",
      $user_id,
      getenv("REMOTE_ADDR"),
      json_encode([
        "error"           => $model->error,
        "packet_id"       => $model->packet_id,
        "options_bitmask" => $model->packet->getOptionsBitmask(),
        "name"            => $model->packet->getPacketName(),
        "format"          => $model->packet->getPacketFormat(),
        "remarks"         => $model->packet->getPacketRemarks(false),
      ])
    );
  }

}
