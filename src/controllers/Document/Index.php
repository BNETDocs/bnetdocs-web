<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Document;
use \BNETDocs\Models\Document\Index as DocumentIndexModel;
use \BNETDocs\Views\Document\IndexHtml as DocumentIndexHtmlView;
use \BNETDocs\Views\Document\IndexJSON as DocumentIndexJSONView;
use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Gravatar;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;
use \DateTime;
use \DateTimeZone;

class Index extends Controller {

  public function &run(Router &$router, View &$view, array &$args) {

    $model               = new DocumentIndexModel();
    $model->documents    = Document::getAllDocuments();

    // Alphabetically sort the documents for HTML
    if ($view instanceof DocumentIndexHtmlView && $model->documents) {
      usort($model->documents, function($a, $b){
        $a1 = $a->getTitle();
        $b1 = $b->getTitle();
        if ($a1 == $b1) return 0;
        return ($a1 < $b1 ? -1 : 1);
      });
    }

    // Remove documents that are not published
    if ($model->documents) {
      $i = count($model->documents) - 1;
      while ($i >= 0) {
        if (!($model->documents[$i]->getOptionsBitmask()
          & Document::OPTION_PUBLISHED)) {
          unset($model->documents[$i]);
        }
        --$i;
      }
    }

    // Objectify for JSON
    if ($view instanceof DocumentIndexJSONView) {
      $model->timestamp = new DateTime("now", new DateTimeZone("UTC"));
      $documents = [];
      foreach ($model->documents as $document) {
        $user = $document->getUser();
        if ($user) {
          $user = [
            "avatar_url" => "https:"
              . (new Gravatar($user->getEmail()))->getUrl(null, "identicon"),
            "id"     => $user->getId(),
            "name"   => $user->getName(),
            "url"    => Common::relativeUrlToAbsolute(
              "/user/" . $user->getId() . "/"
              . Common::sanitizeForUrl($user->getName())
            )
          ];
        }
        $documents[] = [
          "content"          => $document->getContent(false),
          "created_datetime" => self::renderDateTime(
                                  $document->getCreatedDateTime()
                                ),
          "edited_count"     => $document->getEditedCount(),
          "edited_datetime"  => self::renderDateTime(
                                  $document->getEditedDateTime()
                                ),
          "id"               => $document->getId(),
          "options_bitmask"  => $document->getOptionsBitmask(),
          "title"            => $document->getTitle(),
          "user"             => $user
        ];
      }
      $model->documents = $documents;
    }

    // Post-filter summary of documents
    $model->sum_documents = count($model->documents);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();
    $model->_responseTTL = 0;

    return $model;

  }

  protected static function renderDateTime($obj) {
    if (!$obj instanceof DateTime) return $obj;
    return $obj->format("r");
  }

}
