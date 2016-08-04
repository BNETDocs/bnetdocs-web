<?php

namespace BNETDocs\Views\Attachment;

use \CarlBennett\MVC\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Attachment\Download as DownloadModel;
use \DateTime;

class DownloadRaw extends View {

  public function getMimeType() {
    return "application/octet-stream";
  }

  public function render(Model &$model) {
    if (!$model instanceof DownloadModel) {
      throw new IncorrectModelException();
    }
    $model->extra_headers = [
      "Content-Disposition" => "attachment;filename=\""
        . $model->attachment->getFilename() . "\"",
      "Content-Length" => (string) strlen($model->attachment->getContent()),
      "Last-Modified" => $model->attachment->getCreatedDateTime()->format(
        DateTime::RFC1123
      )
    ];
    echo $model->attachment->getContent();
  }

}
