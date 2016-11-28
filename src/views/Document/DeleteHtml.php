<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Models\Document\Delete as DocumentDeleteModel;
use \CarlBennett\MVC\Libraries\Exceptions\IncorrectModelException;
use \CarlBennett\MVC\Libraries\Model;
use \CarlBennett\MVC\Libraries\Template;
use \CarlBennett\MVC\Libraries\View;

class DeleteHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentDeleteModel) {
      throw new IncorrectModelException();
    }
    (new Template($model, "Document/Delete"))->render();
  }

}
