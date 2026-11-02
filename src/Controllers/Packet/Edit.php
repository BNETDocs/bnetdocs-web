<?php /* vim: set colorcolumn= expandtab shiftwidth=2 softtabstop=2 tabstop=4 smarttab: */
namespace BNETDocs\Controllers\Packet;

use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\Core\HttpCode;
use \BNETDocs\Libraries\Core\Router;
use \BNETDocs\Libraries\Discord\EmbedField as DiscordEmbedField;
use \BNETDocs\Libraries\EventLog\Logger;
use \BNETDocs\Libraries\Packet\Packet;
use \BNETDocs\Libraries\Product;
use \BNETDocs\Models\Packet\Form as FormModel;
use \OutOfBoundsException;
use \UnexpectedValueException;

class Edit extends \BNETDocs\Controllers\Base
{
  public function __construct()
  {
    $this->model = new FormModel();
  }

  public function invoke(?array $args): bool
  {
    if (!$this->model->active_user || !$this->model->active_user->getOption(\BNETDocs\Libraries\User\User::OPTION_ACL_PACKET_MODIFY))
    {
      $this->model->error = $this->model->active_user ? FormModel::ERROR_ACL_NOT_SET : FormModel::ERROR_NOT_LOGGED_IN;
      $this->model->_responseCode = HttpCode::HTTP_UNAUTHORIZED;
      return true;
    }

    $this->model->form_fields = Router::query();
    $this->model->products = Product::getAllProducts();

    $id = $this->model->form_fields['id'] ?? null;
    try { if (!is_null($id)) $this->model->packet = new Packet($id); }
    catch (\UnexpectedValueException) { $this->model->packet = null; }

    if (is_null($this->model->packet))
    {
      $this->model->error = FormModel::ERROR_NOT_FOUND;
      $this->model->_responseCode = HttpCode::HTTP_NOT_FOUND;
      return true;
    }

    $this->model->comments = Comment::getAll(Comment::PARENT_TYPE_PACKET, $id);

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

    if ($this->model->error === false)
    {
      $event = Logger::initEvent(
        \BNETDocs\Libraries\EventLog\EventTypes::PACKET_EDITED,
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

      if ($event->commit())
      {
        $packet = $this->model->packet;

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

          'Authored by' => !\is_null($packet->getUserId()) ? $packet->getUser()->getAsMarkdown() : '*Anonymous*',
          'Edited by' => $this->model->active_user->getAsMarkdown(),
        ]);
        $embed->addField(new DiscordEmbedField('Format', '```' . \PHP_EOL . $format . \PHP_EOL . '```', false));
        $embed->setDescription($packet->isMarkdown() ? $remarks : '```' . \PHP_EOL . $remarks . \PHP_EOL . '```');
        Logger::logToDiscord($event, $embed);
      }
    }

    $this->model->_responseCode = !$this->model->error ? HttpCode::HTTP_OK : HttpCode::HTTP_INTERNAL_SERVER_ERROR;
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

    $this->model->error = false;

    try { $this->model->packet->setApplicationLayerId($application_layer); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_APPLICATION_LAYER_ID; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_APPLICATION_LAYER_ID; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setBrief($brief); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_BRIEF; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_BRIEF; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setDirection((int) $direction); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_DIRECTION; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_DIRECTION; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setPacketId($packet_id); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_PACKET_ID; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setName($name); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_NAME; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_NAME; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setFormat($format); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_FORMAT; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_FORMAT; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setRemarks($remarks); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_REMARKS; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_REMARKS; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setTransportLayerId($transport_layer); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_TRANSPORT_LAYER_ID; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_TRANSPORT_LAYER_ID; }
    if ($this->model->error !== false) return;

    try { $this->model->packet->setUsedBy($used_by); }
    catch (OutOfBoundsException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_USED_BY; }
    catch (UnexpectedValueException) { $this->model->error = FormModel::ERROR_OUTOFBOUNDS_USED_BY; }
    if ($this->model->error !== false) return;

    $this->model->packet->setOption(Packet::OPTION_DEPRECATED, $deprecated ? true : false);
    $this->model->packet->setOption(Packet::OPTION_MARKDOWN, $markdown ? true : false);
    $this->model->packet->setOption(Packet::OPTION_PUBLISHED, $published ? true : false);
    $this->model->packet->setOption(Packet::OPTION_RESEARCH, $research ? true : false);
    $this->model->packet->incrementEdited();

    $this->model->error = $this->model->packet->commit() ? false : FormModel::ERROR_INTERNAL;
  }
}
