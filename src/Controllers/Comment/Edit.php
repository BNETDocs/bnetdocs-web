<?php

namespace BNETDocs\Controllers\Comment;

use \BNETDocs\Libraries\Authentication;
use \BNETDocs\Libraries\Comment;
use \BNETDocs\Libraries\EventTypes;
use \BNETDocs\Libraries\Exceptions\CommentNotFoundException;
use \BNETDocs\Libraries\Logger;
use \BNETDocs\Libraries\User;

use \BNETDocs\Models\Comment\Edit as CommentEditModel;

use \CarlBennett\MVC\Libraries\Common;
use \CarlBennett\MVC\Libraries\Controller;
use \CarlBennett\MVC\Libraries\Exceptions\QueryException;
use \CarlBennett\MVC\Libraries\Router;
use \CarlBennett\MVC\Libraries\View;

use \DateTime;
use \DateTimeZone;
use \InvalidArgumentException;
use \UnexpectedValueException;

class Edit extends Controller {
  public function &run( Router &$router, View &$view, array &$args ) {

    $query_data = $router->getRequestQueryArray();
    $post_data = $router->getRequestBodyArray();

    $model = new CommentEditModel();
    $model->user = Authentication::$user;
    $model->id = (isset( $query_data[ 'id' ]) ? $query_data[ 'id' ] : null);
    $model->content = (
      isset( $post_data[ 'content' ]) ? $post_data[ 'content' ] : null
    );

    try { $model->comment = new Comment( $model->id ); }
    catch ( CommentNotFoundException $e ) { $model->comment = null; }
    catch ( InvalidArgumentException $e ) { $model->comment = null; }

    $model->acl_allowed = ( $model->user && (
      $model->user->getOption( User::OPTION_ACL_COMMENT_MODIFY ) ||
      $model->user->getId() == $model->comment->getUserId()
    ));

    if ( is_null( $model->comment )) {
      $model->error = 'NOT_FOUND';
    } else {
      if ( is_null( $model->content )) {
        $model->content = $model->comment->getContent( false );
      }

      $model->parent_type = $model->comment->getParentType();
      $model->parent_id   = $model->comment->getParentId();

      switch ( $model->parent_type ) {
        case Comment::PARENT_TYPE_DOCUMENT:
          $model->return_url = '/document/' . $model->parent_id; break;
        case Comment::PARENT_TYPE_COMMENT:
          $model->return_url = '/comment/' . $model->parent_id; break;
        case Comment::PARENT_TYPE_NEWS_POST:
          $model->return_url = '/news/' . $model->parent_id; break;
        case Comment::PARENT_TYPE_PACKET:
          $model->return_url = '/packet/' . $model->parent_id; break;
        case Comment::PARENT_TYPE_SERVER:
          $model->return_url = '/server/' . $model->parent_id; break;
        case Comment::PARENT_TYPE_USER:
          $model->return_url = '/user/' . $model->parent_id; break;
        default: throw new UnexpectedValueException(
          'Parent type: ' . $model->parent_type
        );
      }
      $model->return_url = Common::relativeUrlToAbsolute( $model->return_url );

      if ( $router->getRequestMethod() == 'POST' ) {
        $this->tryModify( $router, $model );
      }
    }

    $view->render( $model );
    $model->_responseCode = ( $model->acl_allowed ? 200 : 403 );
    return $model;
  }

  protected function tryModify( Router &$router, CommentEditModel &$model ) {
    if ( !isset( $model->user )) {
      $model->error = 'NOT_LOGGED_IN';
      return;
    }
    if ( !$model->acl_allowed ) {
      $model->error = 'ACL_NOT_SET';
      return;
    }

    $model->error = false;

    $id          = (int) $model->id;
    $parent_type = (int) $model->parent_type;
    $parent_id   = (int) $model->parent_id;
    $user_id     = $model->user->getId();

    $log_key = null;
    switch ( $parent_type ) {
      case Comment::PARENT_TYPE_DOCUMENT:
        $log_key = EventTypes::COMMENT_EDITED_DOCUMENT; break;
      case Comment::PARENT_TYPE_COMMENT:
        $log_key = EventTypes::COMMENT_EDITED_COMMENT; break;
      case Comment::PARENT_TYPE_NEWS_POST:
        $log_key = EventTypes::COMMENT_EDITED_NEWS; break;
      case Comment::PARENT_TYPE_PACKET:
        $log_key = EventTypes::COMMENT_EDITED_PACKET; break;
      case Comment::PARENT_TYPE_SERVER:
        $log_key = EventTypes::COMMENT_EDITED_SERVER; break;
      case Comment::PARENT_TYPE_USER:
        $log_key = EventTypes::COMMENT_EDITED_USER; break;
      default: throw new UnexpectedValueException(
        'Parent type: ' . $parent_type
      );
    }

    try {

      $model->comment->setContent( $model->content );
      $model->comment->setEditedCount( $model->comment->getEditedCount() + 1 );
      $model->comment->setEditedDateTime(
        new DateTime( 'now', new DateTimeZone( 'Etc/UTC' ))
      );

      $success = $model->comment->save();

    } catch ( QueryException $e ) {

      // SQL error occurred. We can show a friendly message to the user while
      // also notifying this problem to staff.
      Logger::logException( $e );

      $success = false;

    }

    if ( !$success ) {
      $model->error = 'INTERNAL_ERROR';
    } else {
      $model->error = false;
    }

    Logger::logEvent(
      $log_key,
      $user_id,
      getenv( 'REMOTE_ADDR' ),
      json_encode([
        'error'       => $model->error,
        'comment_id'  => $id,
        'content'     => $model->content,
        'parent_type' => $parent_type,
        'parent_id'   => $parent_id
      ])
    );
  }
}
