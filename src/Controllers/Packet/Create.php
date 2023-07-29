<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Product;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Models\Packet\Form as FormModel;
use \OutOfBoundsException;

class Create extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new FormModel();
  }

  public function invoke(?array $args): bool
  {
    if (!$this->model->active_user || !$this->model->active_user->getOption(\BNETDocs\Libraries\User::OPTION_ACL_PACKET_CREATE))
    {
      $this->model->_responseCode = 401;
      $this->model->error = FormModel::ERROR_ACL_DENIED;
      return true;
    }

    $this->model->form_fields = Router::query();
    $this->model->products = Product::getAllProducts();
    $this->model->packet = new \BNETDocs\Libraries\Packet(null);

    self::assignDefault($this->model->form_fields, 'application_layer', $this->model->packet->getApplicationLayerId());
    self::assignDefault($this->model->form_fields, 'brief', $this->model->packet->getBrief(false));
    self::assignDefault($this->model->form_fields, 'deprecated', $this->model->packet->isDeprecated());
    self::assignDefault($this->model->form_fields, 'direction', $this->model->packet->getDirection());
    self::assignDefault($this->model->form_fields, 'format', $this->model->packet->getFormat());
    self::assignDefault($this->model->form_fields, 'markdown', $this->model->packet->isMarkdown());
    self::assignDefault($this->model->form_fields, 'name', $this->model->packet->getName());
    self::assignDefault($this->model->form_fields, 'packet_id', $this->model->packet->getPacketId(true));
    self::assignDefault($this->model->form_fields, 'published', $this->model->packet->isPublished());
    self::assignDefault($this->model->form_fields, 'remarks', $this->model->packet->getRemarks(false));
    self::assignDefault($this->model->form_fields, 'research', $this->model->packet->isInResearch());
    self::assignDefault($this->model->form_fields, 'transport_layer', $this->model->packet->getTransportLayerId());
    self::assignDefault($this->model->form_fields, 'used_by', Product::getProductsFromIds($this->model->packet->getUsedBy()));

    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost($this->model);
    else $this->model->error = FormModel::ERROR_NONE;

    if ($this->model->error === FormModel::ERROR_SUCCESS)
    {
      \BNETDocs\Libraries\EventLog\Event::log(
        \BNETDocs\Libraries\EventLog\EventTypes::PACKET_CREATED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        [
          'application_layer' => $this->model->packet->getApplicationLayer()->getLabel(),
          'brief' => $this->model->packet->getBrief(false),
          'created_dt' => $this->model->packet->getCreatedDateTime(),
          'deprecated' => $this->model->packet->isDeprecated(),
          'direction' => $this->model->packet->getDirectionLabel(),
          'draft' => !$this->model->packet->isPublished(),
          'edited_dt' => $this->model->packet->getEditedDateTime(),
          'edits' => $this->model->packet->getEditedCount(),
          'format' => $this->model->packet->getFormat(),
          'id' => $this->model->packet->getId(),
          'markdown' => $this->model->packet->isMarkdown(),
          'name' => $this->model->packet->getName(),
          'owner' => $this->model->packet->getUser(),
          'remarks' => $this->model->packet->getRemarks(false),
          'research' => $this->model->packet->isInResearch(),
          'transport_layer' => $this->model->packet->getTransportLayer()->getLabel(),
          'used_by' => $this->model->packet->getUsedBy(),
        ]
      );
    }

    $this->model->_responseCode = ($this->model->error === FormModel::ERROR_SUCCESS ? 200 : 500);
    return true;
  }

  protected static function assignDefault(array &$form_fields, string $key, mixed $value): void
  {
    if (isset($form_fields[$key])) return;
    $form_fields[$key] = $value;
  }

  protected function handlePost(): void
  {
    $application_layer = $this->model->form_fields['application_layer'] ?? null;
    $brief = $this->model->form_fields['brief'] ?? null;
    $deprecated = $this->model->form_fields['deprecated'] ?? null;
    $direction = $this->model->form_fields['direction'] ?? null;
    $format = $this->model->form_fields['format'] ?? null;
    $markdown = $this->model->form_fields['markdown'] ?? null;
    $name = $this->model->form_fields['name'] ?? null;
    $packet_id = $this->model->form_fields['packet_id'] ?? null;
    $published = $this->model->form_fields['published'] ?? null;
    $remarks = $this->model->form_fields['remarks'] ?? null;
    $research = $this->model->form_fields['research'] ?? null;
    $transport_layer = $this->model->form_fields['transport_layer'] ?? null;
    $used_by = $this->model->form_fields['used_by'] ?? [];

    try { $this->model->packet->setApplicationLayerId($application_layer); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_APPLICATION_LAYER_ID; return; }

    try { $this->model->packet->setBrief($brief); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_BRIEF; return; }

    try { $this->model->packet->setDirection((int) $direction); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_DIRECTION; return; }

    try { $this->model->packet->setPacketId($packet_id); }
    catch (\InvalidArgumentException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID; return; }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID; return; }

    try { $this->model->packet->setName($name); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_NAME; return; }

    try { $this->model->packet->setFormat($format); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_FORMAT; return; }

    try { $this->model->packet->setRemarks($remarks); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_REMARKS; return; }

    try { $this->model->packet->setTransportLayerId($transport_layer); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_TRANSPORT_LAYER_ID; return; }

    try { $this->model->packet->setUsedBy($used_by); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_USED_BY; return; }

    $this->model->packet->setDeprecated($deprecated ? true : false);
    $this->model->packet->setInResearch($research ? true : false);
    $this->model->packet->setMarkdown($markdown ? true : false);
    $this->model->packet->setPublished($published ? true : false);
    $this->model->packet->setUser($this->model->active_user);

    $this->model->error = $this->model->packet->commit() ? FormModel::ERROR_SUCCESS : FormModel::ERROR_INTERNAL;
  }
}
