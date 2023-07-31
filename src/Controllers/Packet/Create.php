<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */

namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\EventLog\Logger;
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
    $packet = &$this->model->packet;

    self::assignDefault($this->model->form_fields, 'application_layer', $packet->getApplicationLayerId());
    self::assignDefault($this->model->form_fields, 'brief', $packet->getBrief(false));
    self::assignDefault($this->model->form_fields, 'deprecated', $packet->isDeprecated());
    self::assignDefault($this->model->form_fields, 'direction', $packet->getDirection());
    self::assignDefault($this->model->form_fields, 'format', $packet->getFormat());
    self::assignDefault($this->model->form_fields, 'markdown', $packet->isMarkdown());
    self::assignDefault($this->model->form_fields, 'name', $packet->getName());
    self::assignDefault($this->model->form_fields, 'packet_id', $packet->getPacketId(true));
    self::assignDefault($this->model->form_fields, 'published', $packet->isPublished());
    self::assignDefault($this->model->form_fields, 'remarks', $packet->getRemarks(false));
    self::assignDefault($this->model->form_fields, 'research', $packet->isInResearch());
    self::assignDefault($this->model->form_fields, 'transport_layer', $packet->getTransportLayerId());
    self::assignDefault($this->model->form_fields, 'used_by', Product::getProductsFromIds($packet->getUsedBy()));

    if (Router::requestMethod() == Router::METHOD_POST) $this->handlePost($this->model);
    else $this->model->error = FormModel::ERROR_NONE;

    if ($this->model->error === FormModel::ERROR_SUCCESS)
    {
      $event = Logger::initEvent(
        \BNETDocs\Libraries\EventLog\EventTypes::PACKET_CREATED,
        $this->model->active_user,
        getenv('REMOTE_ADDR'),
        [
          'application_layer' => $packet->getApplicationLayer()->getLabel(),
          'brief' => $packet->getBrief(false),
          'created_dt' => $packet->getCreatedDateTime(),
          'deprecated' => $packet->isDeprecated(),
          'direction' => $packet->getDirectionLabel(),
          'draft' => !$packet->isPublished(),
          'edited_dt' => $packet->getEditedDateTime(),
          'edits' => $packet->getEditedCount(),
          'format' => $packet->getFormat(),
          'id' => $packet->getId(),
          'markdown' => $packet->isMarkdown(),
          'name' => $packet->getName(),
          'owner' => $packet->getUser(),
          'remarks' => $packet->getRemarks(false),
          'research' => $packet->isInResearch(),
          'transport_layer' => $packet->getTransportLayer()->getLabel(),
          'used_by' => $packet->getUsedBy(),
        ]
      );

      if ($event->commit())
      {
        $brief = $packet->getBrief(false);
        $format = $packet->getFormat();
        $remarks = $packet->getRemarks(false);

        $offset = 13; // char count of code block, end-of-line, and ellipsis addons
        if (\strlen($brief) - $offset > DiscordEmbedField::MAX_VALUE)
        {
          $brief = \substr($brief, 0, DiscordEmbedField::MAX_VALUE - $offset) . '…';
        }
        if (\strlen($format) - $offset > DiscordEmbedField::MAX_VALUE)
        {
          $format = \substr($format, 0, DiscordEmbedField::MAX_VALUE - $offset) . '…';
        }
        if (\strlen($remarks) - $offset > DiscordEmbedField::MAX_VALUE)
        {
          $remarks = \substr($remarks, 0, DiscordEmbedField::MAX_VALUE - $offset) . '…';
        }

        $used_by = '';
        foreach ($packet->getUsedBy() as $product)
        {
          if (!empty($used_by)) $used_by .= ', ';
          $used_by .= $product->getLabel();
        }
        if (empty($used_by)) $used_by = '*Unknown*';

        $embed = Logger::initDiscordEmbed($event, $packet->getURI(), [
          'Direction' => $packet->getDirectionLabel(),
          'Id' => $packet->getPacketId(true),
          'Name' => $packet->getName(),
          'Brief' => !empty($brief) ? $brief : '*empty*',

          'Deprecated' => $packet->isDeprecated() ? ':white_check_mark:' : ':x:',
          'Draft' => !$packet->isPublished() ? ':white_check_mark:' : ':x:',
          'Markdown' => $packet->isMarkdown() ? ':white_check_mark:' : ':x:',
          'In research' => $packet->isInResearch() ? ':white_check_mark:' : ':x:',

          'Application layer' => $packet->getApplicationLayer()->getLabel(),
          'Transport layer' => $packet->getTransportLayer()->getTag(),
          'Used by' => $used_by,
        ]);
        $embed->addField(new DiscordEmbedField('Format', '```' . \PHP_EOL . $format . \PHP_EOL . '```', false));
        $embed->setDescription($packet->isMarkdown() ? $remarks : '```' . \PHP_EOL . $remarks . \PHP_EOL . '```');
        Logger::logToDiscord($event, $embed);
      }
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
