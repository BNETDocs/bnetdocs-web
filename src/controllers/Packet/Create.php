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

class Create extends Controller
{
  public function &run(Router &$router, View &$view, array &$args)
  {
    $model = new FormModel();
    $model->active_user = Authentication::$user;

    if (!$model->active_user || !$model->active_user->getOption(User::OPTION_ACL_PACKET_CREATE))
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
    $model->packet = new Packet(null);

    self::assignDefault($model->form_fields, 'application_layer', $model->packet->getApplicationLayerId());
    self::assignDefault($model->form_fields, 'brief', $model->packet->getBrief(false));
    self::assignDefault($model->form_fields, 'deprecated', $model->packet->isDeprecated());
    self::assignDefault($model->form_fields, 'direction', $model->packet->getDirection());
    self::assignDefault($model->form_fields, 'format', $model->packet->getFormat());
    self::assignDefault($model->form_fields, 'markdown', $model->packet->isMarkdown());
    self::assignDefault($model->form_fields, 'name', $model->packet->getName());
    self::assignDefault($model->form_fields, 'packet_id', $model->packet->getPacketId(true));
    self::assignDefault($model->form_fields, 'published', $model->packet->isPublished());
    self::assignDefault($model->form_fields, 'remarks', $model->packet->getRemarks(false));
    self::assignDefault($model->form_fields, 'research', $model->packet->isInResearch());
    self::assignDefault($model->form_fields, 'transport_layer', $model->packet->getTransportLayerId());

    if ($router->getRequestMethod() == 'GET')
    {
      self::assignDefault($model->form_fields, 'used_by', Product::getProductsFromIds($model->packet->getUsedBy()));
    }

    if ($router->getRequestMethod() == 'POST')
    {
      $this->handlePost($model);
    }

    if ($model->error === FormModel::ERROR_SUCCESS)
    {
      Logger::logEvent(
        EventTypes::PACKET_CREATED,
        $model->active_user->getId(),
        getenv('REMOTE_ADDR'),
        json_encode([
          'application_layer' => $model->packet->getApplicationLayer()->getLabel(),
          'brief' => $model->packet->getBrief(false),
          'created_dt' => $model->packet->getCreatedDateTime(),
          'deprecated' => $model->packet->isDeprecated(),
          'direction' => $model->packet->getDirectionLabel(),
          'draft' => !$model->packet->isPublished(),
          'edited_dt' => $model->packet->getEditedDateTime(),
          'edits' => $model->packet->getEditedCount(),
          'format' => $model->packet->getFormat(),
          'id' => $model->packet->getId(),
          'markdown' => $model->packet->isMarkdown(),
          'name' => $model->packet->getName(),
          'owner' => $model->packet->getUser(),
          'remarks' => $model->packet->getRemarks(false),
          'research' => $model->packet->isInResearch(),
          'transport_layer' => $model->packet->getTransportLayer()->getLabel(),
          'used_by' => $model->packet->getUsedBy(),
        ])
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
    $application_layer = $model->form_fields['application_layer'] ?? null;
    $brief = $model->form_fields['brief'] ?? null;
    $deprecated = $model->form_fields['deprecated'] ?? null;
    $direction = $model->form_fields['direction'] ?? null;
    $format = $model->form_fields['format'] ?? null;
    $markdown = $model->form_fields['markdown'] ?? null;
    $name = $model->form_fields['name'] ?? null;
    $packet_id = $model->form_fields['packet_id'] ?? null;
    $published = $model->form_fields['published'] ?? null;
    $remarks = $model->form_fields['remarks'] ?? null;
    $research = $model->form_fields['research'] ?? null;
    $transport_layer = $model->form_fields['transport_layer'] ?? null;
    $used_by = $model->form_fields['used_by'] ?? [];

    try
    {
      $model->packet->setApplicationLayerId($application_layer);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_APPLICATION_LAYER_ID;
      return;
    }

    try
    {
      $model->packet->setBrief($brief);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_BRIEF;
      return;
    }

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
      $model->packet->setTransportLayerId($transport_layer);
    }
    catch (OutOfBoundsException $e)
    {
      $model->error = FormModel::ERROR_OUTOFBOUNDS_TRANSPORT_LAYER_ID;
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
    $model->packet->setUser($model->active_user);

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
