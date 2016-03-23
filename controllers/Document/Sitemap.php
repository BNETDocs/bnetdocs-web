<?php

namespace BNETDocs\Controllers\Document;

use \BNETDocs\Libraries\Common;
use \BNETDocs\Libraries\Controller;
use \BNETDocs\Libraries\Document;
use \BNETDocs\Libraries\Exceptions\UnspecifiedViewException;
use \BNETDocs\Libraries\Gravatar;
use \BNETDocs\Libraries\Router;
use \BNETDocs\Libraries\UserSession;
use \BNETDocs\Models\Document\Sitemap as DocumentSitemapModel;
use \BNETDocs\Views\Document\SitemapHtml as DocumentSitemapHtmlView;
use \BNETDocs\Views\Document\SitemapJSON as DocumentSitemapJSONView;
use \DateTime;
use \DateTimeZone;

class Sitemap extends Controller {

  public function run(Router &$router) {
    switch ($router->getRequestPathExtension()) {
      case "htm": case "html": case "":
        $view = new DocumentSitemapHtmlView();
      break;
      case "json":
        $view = new DocumentSitemapJSONView();
      break;
      default:
        throw new UnspecifiedViewException();
    }
    $model = new DocumentSitemapModel();
    
    $model->documents     = Document::getAllDocuments();
    $model->user_session  = UserSession::load($router);

    // Alphabetically sort the documents for HTML
    if ($view instanceof DocumentSitemapHtmlView && $model->documents) {
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
    if ($view instanceof DocumentSitemapJSONView) {
      $model->timestamp = new DateTime("now", new DateTimeZone("UTC"));
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
            "avatar_url" => "https:"
              . (new Gravatar($user->getEmail()))->getUrl(null, "identicon"),
            "id"     => $user->getId(),
            "name"   => $user->getName(),
            "url"    => Common::relativeUrlToAbsolute(
              "/user/" . $user->getId() . "/"
              . Common::sanitizeForUrl($user->getName())
            )
          ]
        ];
      }
      $model->documents = $documents;
    }

    // Post-filter summary of documents
    $model->sum_documents = count($model->documents);

    ob_start();
    $view->render($model);
    $router->setResponseCode(200);
    $router->setResponseTTL(0);
    $router->setResponseHeader("Content-Type", $view->getMimeType());
    $router->setResponseContent(ob_get_contents());
    ob_end_clean();
  }
  
  protected static function renderDateTime($obj) {
    if (!$obj instanceof DateTime) return $obj;
    return $obj->format("r");
  }

}
