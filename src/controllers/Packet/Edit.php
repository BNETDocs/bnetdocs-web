<?php

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Product;
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
use \OutOfBoundsException;

class Edit extends Controller {
  public function &run(Router &$router, View &$view, array &$args) {
    $data              = $router->getRequestQueryArray();
    $model             = new PacketEditModel();
    $model->comments   = null;
    $model->deprecated = null;
    $model->error      = null;
    $model->format     = null;
    $model->id         = null;
    $model->markdown   = null;
    $model->name       = null;
    $model->packet     = null;
    $model->packet_id  = (isset($data['id']) ? $data['id'] : null);
    $model->products   = Product::getAllProducts();
    $model->published  = null;
    $model->remarks    = null;
    $model->research   = null;
    $model->used_by    = null;
    $model->user       = Authentication::$user;

    $model->acl_allowed = ($model->user && $model->user->getOption(
      User::OPTION_ACL_PACKET_MODIFY
    ));

    try { $model->packet = new Packet($model->packet_id); }
    catch (PacketNotFoundException $e) { $model->packet = null; }
    catch (InvalidArgumentException $e) { $model->packet = null; }

    if ($model->packet === null) {
      $model->error = 'NOT_FOUND';
    } else {
      $model->comments = Comment::getAll(
        Comment::PARENT_TYPE_PACKET, $model->packet_id
      );

      $model->deprecated = $model->packet->isDeprecated();
      $model->id         = $model->packet->getPacketId(true);
      $model->name       = $model->packet->getName();
      $model->format     = $model->packet->getFormat();
      $model->remarks    = $model->packet->getRemarks(false);
      $model->research   = $model->packet->isInResearch();
      $model->markdown   = $model->packet->isMarkdown();
      $model->published  = $model->packet->isPublished();
      $model->used_by    = $this->getUsedBy($model->packet);

      if ($router->getRequestMethod() == 'POST') {
        $this->handlePost($router, $model);
      }
    }

    $view->render($model);
    $model->_responseCode = ($model->acl_allowed ? 200 : 403);
    return $model;
  }

  protected function handlePost(Router &$router, PacketEditModel &$model) {
    if (!$model->acl_allowed) {
      $model->error = 'ACL_NOT_SET';
      return;
    }
    if (!isset(Common::$database)) {
      Common::$database = DatabaseDriver::getDatabaseObject();
    }

    $data       = $router->getRequestBodyArray();
    $id         = (isset($data['id'        ]) ? $data['id'        ] : null);
    $name       = (isset($data['name'      ]) ? $data['name'      ] : null);
    $format     = (isset($data['format'    ]) ? $data['format'    ] : null);
    $remarks    = (isset($data['remarks'   ]) ? $data['remarks'   ] : null);
    $markdown   = (isset($data['markdown'  ]) ? $data['markdown'  ] : null);
    $content    = (isset($data['content'   ]) ? $data['content'   ] : null);
    $deprecated = (isset($data['deprecated']) ? $data['deprecated'] : null);
    $research   = (isset($data['research'  ]) ? $data['research'  ] : null);
    $published  = (isset($data['published' ]) ? $data['published' ] : null);
    $used_by    = (isset($data['used_by'   ]) ? $data['used_by'   ] : null);

    $model->id         = $id;
    $model->name       = $name;
    $model->format     = $format;
    $model->remarks    = $remarks;
    $model->markdown   = $markdown;
    $model->content    = $content;
    $model->deprecated = $deprecated;
    $model->research   = $research;
    $model->published  = $published;

    if (empty($name)) {
      $model->error = 'EMPTY_NAME';
    } else if (empty($format)) {
      $model->error = 'EMPTY_FORMAT';
    } else if (empty($remarks)) {
      $model->error = 'EMPTY_REMARKS';
    }

    $user_id = $model->user->getId();

    try
    {
      $model->packet->setDeprecated($model->deprecated ? true : false);
      $model->packet->setEditedCount($model->packet->getEditedCount() + 1);
      $model->packet->setEditedDateTime(new DateTime('now'));
      $model->packet->setFormat($model->format);
      $model->packet->setInResearch($model->research ? true : false);
      $model->packet->setMarkdown($model->markdown ? true : false);
      $model->packet->setName($model->name);
      $model->packet->setPacketId($model->id);
      $model->packet->setPublished($model->published ? true : false);
      $model->packet->setRemarks($model->remarks);

      $model->packet->commit();
      $success = true;

      // Used-by is stored in a different table than packet data so it is
      // updated separately.
      $model->packet->setUsedBy($used_by);
    }
    catch (OutOfBoundsException $e)
    {
      // Some value was outside of a boundary
      Logger::logException($e);
      $success = false;
    }
    catch (QueryException $e)
    {
      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException($e);
      $success = false;
    }

    if (!$success) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      EventTypes::PACKET_EDITED,
      $user_id,
      getenv('REMOTE_ADDR'),
      json_encode([
        'error'           => $model->error,
        'id'              => $model->packet->getId(),
        'packet_id'       => $model->packet->getPacketId(true),
        'name'            => $model->packet->getName(),
        'options_bitmask' => $model->packet->getOptions(),
        'format'          => $model->packet->getFormat(),
        'remarks'         => $model->packet->getRemarks(false),
        'used_by'         => $used_by
      ])
    );
  }

  protected function getUsedBy(Packet &$packet) {
    if (is_null($packet)) return null;
    return Product::getProductsFromIds($packet->getUsedBy());
  }
}
