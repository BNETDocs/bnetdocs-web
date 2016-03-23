<?php

namespace BNETDocs\Views\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Exceptions\IncorrectModelException;
use \BNETDocs\Libraries\Gravatar;
use \BNETDocs\Libraries\Model;
use \BNETDocs\Libraries\View;
use \BNETDocs\Models\Document\Sitemap as DocumentSitemapModel;
use \DateTime;

class SitemapJSON extends View {

  public function getMimeType() {
    return "application/json;charset=utf-8";
  }

  public function render(Model &$model) {
    if (!$model instanceof DocumentSitemapModel) {
      throw new IncorrectModelException();
    }
    $documents = [];
    foreach ($model->documents as $document) {
      $user = $document->getUser();
      $documents[] = [
        "content"          => $document->getContent(false),
        "created_datetime" => self::renderDateTime($document->getCreatedDateTime()),
        "edited_count"     => $document->getEditedCount(),
        "edited_datetime"  => self::renderDateTime($document->getEditedDateTime()),
        "id"               => $document->getId(),
        "options_bitmask"  => $document->getOptionsBitmask(),
        "title"            => $document->getTitle(),
        "user"             => [
          "id"     => $user->getId(),
          "name"   => $user->getName(),
          "avatar" => "https:"
            . (new Gravatar($user->getEmail()))->getUrl(null, "identicon"),
          "url"    => Common::relativeUrlToAbsolute(
            "/user/" . $user->getId() . "/"
            . Common::sanitizeForUrl($user->getName())
          )
        ]
      ];
    }
    echo json_encode([
      "documents" => $documents
    ], Common::prettyJSONIfBrowser());
  }

  protected static function renderDateTime($obj) {
    if (!$obj instanceof DateTime) return $obj;
    return $obj->format("r");
  }

}
