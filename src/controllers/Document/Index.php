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

    $model = new DocumentIndexModel();

    $query = $router->getRequestQueryArray();

    $model->order = (
      isset($query['order']) ? $query['order'] : 'title-asc'
    );

    switch ($model->order) {
      case 'created-asc':
        $order = ['created_datetime','ASC']; break;
      case 'created-desc':
        $order = ['created_datetime','DESC']; break;
      case 'id-asc':
        $order = ['id','ASC']; break;
      case 'id-desc':
        $order = ['id','DESC']; break;
      case 'title-asc':
        $order = ['title','ASC']; break;
      case 'title-desc':
        $order = ['title','DESC']; break;
      case 'updated-asc':
        $order = ['edited_datetime','ASC']; break;
      case 'updated-desc':
        $order = ['edited_datetime','DESC']; break;
      case 'user-id-asc':
        $order = [ 'user_id','ASC' ]; break;
      case 'user-id-desc':
        $order = [ 'user_id','DESC' ]; break;
      default:
        $order = null;
    }

    $model->documents = Document::getAllDocuments( $order );

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
      $model->timestamp = new DateTime( 'now', new DateTimeZone( 'Etc/UTC' ));
      $documents = [];
      foreach ($model->documents as $document) {
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
          "user"             => $document->getUser(),
        ];
      }
      $model->documents = $documents;
    }

    // Post-filter summary of documents
    $model->sum_documents = count($model->documents);

    $view->render($model);

    $model->_responseCode = 200;
    $model->_responseHeaders["Content-Type"] = $view->getMimeType();

    return $model;

  }

  protected static function renderDateTime($obj) {
    if (!$obj instanceof DateTime) return $obj;
    return $obj->format("r");
  }

}
