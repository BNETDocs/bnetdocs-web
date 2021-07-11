<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\PacketNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\Packet;
use \BNETDocs\Libraries\Product;
use \BNETDocs\Libraries\User;
use \BNETDocs\Models\Packet\Create as PacketCreateModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \OutOfBoundsException;

class Create extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new PacketCreateModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user || !$model->active_user->getOption(User::OPTION_ACL_PACKET_CREATE))
    {
      $model->error = PacketCreateModel::ERROR_ACL_DENIED;
      $model->_responseCode = 401;
      $view->render($model);
      return $model;
    }

    $model->form_fields = array_merge(
      // Conflicting request query string fields will be overridden by POST-body fields
      $router->getRequestQueryArray() ?? [], $router->getRequestBodyArray() ?? []
    );
    $model->packet = new Packet(null); // TODO : Refactor Packet class

    if ($router->getRequestMethod() == 'POST')
    {
      $this->handlePost($model);
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected function handlePost(PacketCreateModel &$model)
  {
    $deprecated = $model->form_fields['deprecated'] ?? null;
    $format = $model->form_fields['format'] ?? null;
    $markdown = $model->form_fields['markdown'] ?? null;
    $name = $model->form_fields['name'] ?? null;
    $packet_id = $model->form_fields['packet_id'] ?? null;
    $published = $model->form_fields['published'] ?? null;
    $remarks = $model->form_fields['remarks'] ?? null;
    $research = $model->form_fields['research'] ?? null;
    $used_by = $model->form_fields['used_by'] ?? null;

    try
    {
      $model->packet->setPacketId($packet_id);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = PacketCreateModel::ERROR_OUTOFBOUNDS_ID;
      return;
    }

    try
    {
      $model->packet->setPacketName($name);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = PacketCreateModel::ERROR_OUTOFBOUNDS_NAME;
      return;
    }

    try
    {
      $model->packet->setPacketFormat($format);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = PacketCreateModel::ERROR_OUTOFBOUNDS_FORMAT;
      return;
    }

    try
    {
      $model->packet->setPacketRemarks($remarks);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = PacketCreateModel::ERROR_OUTOFBOUNDS_REMARKS;
      return;
    }

    try
    {
      $model->packet->setUsedBy($used_by);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = PacketCreateModel::ERROR_OUTOFBOUNDS_USED_BY;
      return;
    }

    $model->packet->setOption(Packet::OPTION_DEPRECATED, $deprecated);
    $model->packet->setOption(Packet::OPTION_MARKDOWN, $markdown);
    $model->packet->setOption(Packet::OPTION_PUBLISHED, $published);
    $model->packet->setOption(Packet::OPTION_RESEARCH, $research);
    $model->packet->incrementEdited();

    try
    {
      $model->packet->commit();
      $model->error = PacketCreateModel::ERROR_CREATED;
    }
    catch (Exception $e)
    {
      Logger::logException($e);
      $model->error = PacketCreateModel::ERROR_INTERNAL;
    }

    if ($model->error == PacketCreateModel::ERROR_CREATED)
    {
      Logger::logEvent(
        EventTypes::PACKET_CREATED,
        $model->active_user->getId(),
        getenv('REMOTE_ADDR'),
        json_encode(['model' => $model, 'view' => get_class($view)])
      );
    }
  }
}
