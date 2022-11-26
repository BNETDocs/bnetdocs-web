<?php

namespace BNETDocs\Views\Packet;

class ViewPlain extends \BNETDocs\Views\Base\Plain
{
  public static function invoke(\BNETDocs\Interfaces\Model $model) : void
  {
    if (!$model instanceof \BNETDocs\Models\Packet\View)
      throw new \BNETDocs\Exceptions\InvalidModelException($model);

    $model->_responseHeaders['Content-Type'] = self::mimeType();

    $created = $model->packet->getCreatedDateTime()->format(DATE_RFC2822);
    $direction = $model->packet->getDirectionLabel();
    $edited_c = $model->packet->getEditedCount();
    $edited_dt = $model->packet->getEditedDateTime();
    $edited_s = (!$edited_dt ? '(null)' : $edited_dt->format(DATE_RFC2822));
    $format = $model->packet->getFormat();
    $label = $model->packet->getLabel();
    $name = $model->packet->getName();
    $packet_id = $model->packet->getPacketId(true);
    $remarks = $model->packet->getRemarks(false);
    $used_by = $model->used_by;

    $options = [];
    if ($model->packet->isDeprecated()) $options[] = 'Deprecated';
    if ($model->packet->isInResearch()) $options[] = 'In Research';
    if ($model->packet->isMarkdown()) $options[] = 'Markdown';
    if (!$model->packet->isPublished()) $options[] = 'Draft';
    $options = implode(', ', $options);

    printf("# %s\n\n", $label);
    printf("- **Message Id:** `%s`\n", $packet_id);
    printf("- **Message Name:** `%s`\n", $name);
    printf("- **Direction:** %s\n", $direction);
    if (!empty($options)) printf("- **Options:** %s\n", $options);
    printf("- **Added:** %s\n", $created);
    printf("- **Last Edited:** %s (%d edit%s)\n",
      $edited_s, $edited_c, ($edited_c === 1 ? '' : 's')
    );
    echo "\n## Used By\n\n";
    foreach ($used_by as $p) printf("- %s\n", $p->getLabel());
    echo "\n";
    printf("## Message Format\n\n```\n%s\n```\n\n", $format);
    printf("## Remarks\n\n%s\n\n", $remarks);
  }
}
