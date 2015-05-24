<?php

namespace BNETDocs\Views;

use BNETDocs\Libraries\Common;
use BNETDocs\Libraries\Exceptions\IncorrectModelException;
use BNETDocs\Libraries\Model;
use BNETDocs\Libraries\View;
use BNETDocs\Models\News as NewsModel;

class NewsHtml extends View {

  public function getMimeType() {
    return "text/html;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof NewsModel) {
      throw new IncorrectModelException();
    }
    echo "<!DOCTYPE html>";
    echo "<html><head><title>News - BNETDocs</title></head><body>";
    echo "<h1>News</h1>";
    echo "<p>This is a test page for now.</p>";
    echo "</body></html>";
  }

}
