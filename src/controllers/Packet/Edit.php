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
use \BNETDocs\Models\Packet\Form as FormModel;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\DatabaseDriver;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \OutOfBoundsException;

class Edit extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new FormModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user || !$model->active_user->getOption(User::OPTION_ACL_PACKET_MODIFY))
    {
      $model->error = FormModel::ERROR_ACL_DENIED;
      $model->_responseCode = 401;
      $view->render($model);
      return $model;
    }

    $model->form_fields = array_merge(
      // Conflicting request query string fields will be overridden by POST-body fields
      $router->getRequestQueryArray() ?? [], $router->getRequestBodyArray() ?? []
    );
    $model->products = Product::getAllProducts();

    $id = $model->form_fields['id'] ?? null;
    try { if (!is_null($id)) $model->packet = new Packet($id); }
    catch (PacketNotFoundException $e) { $model->packet = null; }
    catch (InvalidArgumentException $e) { $model->packet = null; }

    if (is_null($model->packet))
    {
      $model->error = FormModel::ERROR_NOT_FOUND;
      $model->_responseCode = 404;
      $view->render($model);
      return $model;
    }

    $model->comments = Comment::getAll(Comment::PARENT_TYPE_PACKET, $id);

    self::assignDefault($model->form_fields, 'deprecated', $model->packet->isDeprecated());
    self::assignDefault($model->form_fields, 'packet_id', $model->packet->getPacketId(true));
    self::assignDefault($model->form_fields, 'name', $model->packet->getName());
    self::assignDefault($model->form_fields, 'format', $model->packet->getFormat());
    self::assignDefault($model->form_fields, 'remarks', $model->packet->getRemarks(false));
    self::assignDefault($model->form_fields, 'research', $model->packet->isInResearch());
    self::assignDefault($model->form_fields, 'markdown', $model->packet->isMarkdown());
    self::assignDefault($model->form_fields, 'published', $model->packet->isPublished());
    self::assignDefault($model->form_fields, 'used_by', Product::getProductsFromIds($model->packet->getUsedBy()));

    if ($router->getRequestMethod() == 'POST')
    {
      $this->handlePost($router, $model);
    }

    if ($model->error === FormModel::ERROR_SUCCESS)
    {
      Logger::logEvent(
        EventTypes::PACKET_EDITED,
        $model->active_user->getId(),
        getenv('REMOTE_ADDR'),
        json_encode(['model' => $model, 'view' => get_class($view)])
      );
    }

    $view->render($model);
    $model->_responseCode = 200;
    return $model;
  }

  protected static function assignDefault(&$form_fields, $key, $value)
  {
    if (isset($form_fields[$key])) return;
    $form_fields[$key] = $value;
  }

  protected function handlePost(FormModel &$model)
  {
    $deprecated = $model->form_fields['deprecated'] ?? null;
    $direction = $model->form_fields['direction'] ?? null;
    $format = $model->form_fields['format'] ?? null;
    $markdown = $model->form_fields['markdown'] ?? null;
    $name = $model->form_fields['name'] ?? null;
    $packet_id = $model->form_fields['packet_id'] ?? null;
    $published = $model->form_fields['published'] ?? null;
    $remarks = $model->form_fields['remarks'] ?? null;
    $research = $model->form_fields['research'] ?? null;
    $used_by = $model->form_fields['used_by'] ?? [];

    try
    {
      $model->packet->setDirection((int) $direction);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_DIRECTION;
      return;
    }

    try
    {
      $model->packet->setPacketId($packet_id);
    }
    catch (InvalidArgumentException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID;
      return;
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID;
      return;
    }

    try
    {
      $model->packet->setName($name);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_NAME;
      return;
    }

    try
    {
      $model->packet->setFormat($format);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_FORMAT;
      return;
    }

    try
    {
      $model->packet->setRemarks($remarks);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_REMARKS;
      return;
    }

    try
    {
      $model->packet->setUsedBy($used_by);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_USED_BY;
      return;
    }

    $model->packet->setOption(Packet::OPTION_DEPRECATED, $deprecated ? true : false);
    $model->packet->setOption(Packet::OPTION_MARKDOWN, $markdown ? true : false);
    $model->packet->setOption(Packet::OPTION_PUBLISHED, $published ? true : false);
    $model->packet->setOption(Packet::OPTION_RESEARCH, $research ? true : false);
    $model->packet->incrementEdited();

    try
    {
      $model->packet->commit();
      $model->error = FormModel::ERROR_SUCCESS;
    }
    catch (Exception $e)
    {
      Logger::logException($e);
      $model->error = FormModel::ERROR_INTERNAL;
    }
  }
}
